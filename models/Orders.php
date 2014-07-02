<?php
/**
 * Boutique Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use ecommerce_core\models\Carts;
use ecommerce_core\models\Shipments;
use ecommerce_core\models\PaymentMethods;
use ecommerce_core\models\ShippingMethods;
use cms_core\models\Users;
use cms_core\models\VirtualUsers;
use cms_billing\models\InvoicePositions;
use cms_billing\models\Invoices;
use cms_core\models\Addresses;
use cms_core\extensions\cms\Features;
use cms_core\extensions\cms\Settings;
use DateTime;
use li3_mailer\action\Mailer;
use lithium\g11n\Message;
use lithium\analysis\Logger;
use lithium\util\Validator;

class Orders extends \cms_core\models\Base {

	public static $enum = [
		'status' => [
			'checking-out',
			'checked-out',
			'processing',
			'expired',
			'processed',
			'cancelled',
			'on-backorder',
			'refunding'
		]
	];

	protected $_meta = [
		'source' => 'ecommerce_orders'
	];

	public $belongsTo = [
		'Invoice' => [
			'class' => 'cms_billing\models\Invoice',
			'key' => 'billing_invoice_id'
		],
		'Shipment' => [
			'class' => 'ecommerce_core\models\Shipment',
			'key' => 'ecommerce_shipment_id'
		]
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp',
		'cms_core\extensions\data\behavior\Uuid',
		'cms_core\extensions\data\behavior\ReferenceNumber',
		'cms_core\extensions\data\behavior\StatusChange'
	];

	public static function init() {
		$model = static::_object();
		extract(Message::aliases());

		static::behavior('cms_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('order.number')
		);

		Validator::add('checked', function($value, $format, $options) {
			return $value === '1';
		});

		$model->validates['shipping_method'] = [
			[
				'notEmpty',
				'on' => ['checkoutShipping'],
				'message' => $t('You must select a method.')
			]
		];
		$model->validates['payment_method'] = [
			[
				'notEmpty',
				'on' => ['checkoutPayment'],
				'message' => $t('You must select a method.')
			]
		];
		$model->validates['has_accepted_terms'] = [
			[
				'checked',
				'on' => ['checkoutConfirm'],
				'message' => $t('You must accept the terms.')
			]
		];
	}

	public function shipment($entity) {
		return Shipments::find('first', ['conditions' => ['id' => $entity->ecommerce_shipment_id]]);
	}

	public function user($entity) {
		if ($entity->user_id) {
			return Users::find('first', ['conditions' => ['id' => $entity->user_id]]);
		}
		return VirtualUsers::find('first', ['conditions' => ['id' => $entity->virtual_user_id]]);
	}

	public function cart($entity, array $query = []) {
		return Carts::find('first', [
			'conditions' => [
				'id' => $entity->ecommerce_cart_id
			]
		] + $query);
	}

	public function invoice($entity) {
		return Invoices::find('first', [
			'conditions' => [
				'id' => $entity->billing_invoice_id
			]
		]);
	}

	public function paymentMethod($entity) {
		return PaymentMethods::find('first', [
			'conditions' => [
				'id' => $entity->payment_method
			]
		]);
	}

	public function shippingMethod($entity) {
		return ShippingMethods::find('first', [
			'conditions' => [
				'id' => $entity->shipping_method
			]
		]);
	}

	// FIXME Drop cart parameter and retrieve via entity->cart()?
	public function totalAmount($entity, $user, $cart, $taxZone) {
		$cart = $cart ?: $entity->cart();
		$result = $cart->totalAmount($user, $taxZone);

		$result = $result->add($entity->shippingMethod()->price($user, $cart, $taxZone));
		$result = $result->add($entity->paymentMethod()->price($user, $cart, $taxZone));

		return $result;
	}

	public function totalTax($entity, $user, $cart, $taxZone) {
		return $entity->totalAmount($user, $cart, $taxZone)->getTax();
	}

	public function generateShipment($entity) {
		$shipment = Shipments::create([
			'status' => 'created',
			'method' => $entity->shipping_method
		]);
		$shipment = $entity->address('shipping')->copy($shipment, 'address_');

		if (!$shipment->save()) {
			return false;
		}
		$entity->ecommerce_shipment_id = $shipment->id;
		return $entity->save();
	}

	public function generateInvoice($entity, $user, $cart, array $data = []) {
		extract(Message::aliases());

		$invoice = Invoices::createForUser($user);
		$data += [
			'date' => date('Y-m-d'),
			'status' => 'awaiting-payment',
			'total_currency' => 'EUR',
			'note' => $t('Order No.') . ': ' . $entity->number,
			'terms' => Settings::read('billing.paymentTerms')
		];
		if (!$invoice->save($data)) {
			return false;
		}
		if (!$entity->save(['billing_invoice_id' => $invoice->id])) {
			return false;
		}

		$taxZone = $user->taxZone();
		$currency = $invoice->currency;

		foreach ($cart->positions() as $cartPosition) {
			$product = $cartPosition->product();

			$description  = $product->title . ' ';
			$description .= '(#' . $product->number . ')';

			$price = $cartPosition->product()->price($user, $taxZone);

			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $description,
				'quantity' => $cartPosition->quantity,
				'amount_type' => $price->getType(),
				'amount_currency' => $price->getCurrency(),
				'amount' => $price->getAmount(),
			]);
			if (!$invoicePosition->save(null, ['localize' => false])) {
				return false;
			}
		}

		$price = $entity->shippingMethod()->price($user, $cart, $taxZone);
		if ($price->getAmount()) {
			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $entity->shippingMethod()->title,
				'quantity' => 1,
				'amount_currency' => $price->getCurrency(),
				'amount_type' => $price->getType(),
				'amount' => $price->getAmount()
			]);
			if (!$invoicePosition->save(null, ['localize' => false])) {
				return false;
			}
		}
		$price = $entity->paymentMethod()->price($user, $cart, $taxZone);
		if ($price->getAmount()) {
			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $entity->paymentMethod()->title,
				'amount_currency' => $price->getCurrency(),
				'amount_type' => $price->getType(),
				'amount' => $price->getAmount()
			]);
			if (!$invoicePosition->save(null, ['localize' => false])) {
				return false;
			}
		}

		if (!$invoice->save(['is_locked' => true])) {
			return false;
		}
		return true;
	}

	public function address($entity, $type) {
		$field = $type . '_address_id';
		return Addresses::find('first', ['conditions' => ['id' => $entity->$field]]);
	}

	public static function expire() {
		$data = static::find('all', [
			'conditions' => [
				'status' => 'checking-out'
			]
		]);
		foreach ($data as $item) {
			if ($item->isExpired()) {
				$item->save(['status' => 'expired']);
				$item->cart()->save(['status' => 'expired']);
				Logger::write('debug', "Order `{$item->id}` and associated cart expired.");
			}
		}
	}

	public function isExpired($entity) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->modified);
		return strtotime(Settings::read('checkout.expire'), $date->getTimestamp()) < time();
	}

	public function statusChange($entity, $from, $to) {
		extract(Message::aliases());

		switch ($to) {
			case 'cancelled':
				$invoice = $entity->invoice();
				$shipment = $entity->shipment();

				if (!$invoice->isCancelable() || !$shipment->isCancelable()) {
					return false;
				}
				$result = $invoice->save(['status' => 'cancelled'], [
					'whitelist' => ['status'],
					'validate' => false
				]);
				if (!$result) {
					return false;
				}
				$result = $shipment->save(['status' => 'cancelled'], [
					'whitelist' => ['status'],
					'validate' => false
				]);
				if (!$result) {
					return false;
				}
				return true;
			case 'checked-out':
				$contact = Settings::read('contact.billing');
				$order = $entity;
				$user = $order->user();
				$invoice = $entity->invoice();

				if (!$user->is_notified) {
					return true;
				}
				return Mailer::deliver('order_checked_out', [
					'to' => $user->email,
					'bcc' => $contact['email'],
					'subject' => $t('Your order #{:number}.', [
						'number' => $order->number
					]),
					'data' => [
						'user' => $user,
						'order' => $order
					],
					'attach' => [
						[
							'data' => $invoice->exportAsPdf(),
							'filename' => 'invoice_' . $invoice->number . '.pdf',
							'content-type' => 'application/pdf'
						]
					]
				]);
			default:
				break;
		}
		return true;
	}
}

Orders::init();

?>