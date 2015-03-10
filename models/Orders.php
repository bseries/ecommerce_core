<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace ecommerce_core\models;

use DateTime;
use Exception;
use li3_mailer\action\Mailer;
use lithium\g11n\Message;
use lithium\analysis\Logger;
use lithium\util\Validator;

use base_address\models\Addresses;
use base_core\extensions\cms\Features;
use base_core\extensions\cms\Settings;
use billing_core\models\InvoicePositions;
use billing_core\models\Invoices;
use billing_core\models\ClientGroups;
use ecommerce_core\models\Carts;
use ecommerce_core\models\Shipments;
use ecommerce_core\models\PaymentMethods;
use ecommerce_core\models\ShippingMethods;

class Orders extends \base_core\models\Base {

	use \base_core\models\UserTrait;

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
			'to' => 'billing_core\models\Invoices',
			'key' => 'billing_invoice_id'
		],
		'Shipment' => [
			'to' => 'ecommerce_core\models\Shipments',
			'key' => 'ecommerce_shipment_id'
		],
		'Cart' => [
			'to' => 'ecommerce_core\models\Carts',
			'key' => 'ecommerce_cart_id'
		]
	];

	protected static $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Uuid',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\StatusChange'
	];

	public static function init() {
		$model = static::_object();
		extract(Message::aliases());

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('order.number')
		);

		Validator::add('checked', function($value, $format, $options) {
			return $value === '1';
		});

		$model->validates['shipping_method'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['checkoutShipping'],
				'message' => $t('You must select a method.', ['scope' => 'ecommerce_core'])
			]
		];
		$model->validates['payment_method'] = [
			'notEmpty' => [
				'notEmpty',
				'on' => ['checkoutPayment'],
				'message' => $t('You must select a method.', ['scope' => 'ecommerce_core'])
			]
		];
		$model->validates['has_accepted_terms'] = [
			'checked' => [
				'checked',
				'on' => ['checkoutConfirm'],
				'message' => $t('You must accept the terms.', ['scope' => 'ecommerce_core'])
			]
		];
	}

	// Used during checkout to assign transient addresses while creating them when
	// necessary. Type can be either `billing` or `shipping`. Assumes address
	// has been already validated before. Will update user's preferred address.
	//
	// Expects $entity to have an address object at $entity->shipping_address or
	// $entity->billing_address. These must not have an id set.
	//
	// We will reuse existing addresses but only if don't need to update them. If there is
	// any new data we will create a new address and set the users preferred address to
	// it.
	public function assignTransientAddress($entity, $type, $user) {
		$typeField       = $type == 'billing' ? 'billing_address' : 'shipping_address';
		$typeIdField     = $type == 'billing' ? 'billing_address_id' : 'shipping_address_id';
		$typeMethodField = $type == 'billing' ? 'payment_method' : 'shipping_method';

		// Work on this object and reassign it to $entity back again later.
		// FIXME check if we can drop reassignment as we're working with a ref.
		$address = $entity->{$typeField};

		if ($address->id) {
			throw new Exception("Method will not work with an address id.");
		}
		$exists = Addresses::find('first', [
			'conditions' =>  [
				$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id
			] + $address->data()
		]);
		if ($exists) {
			$address = $exists; // Need id for setting default address later.
			$result = true;
		} else {
			$address = Addresses::create([
				$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id
			] + $address->data());

			$result = $address->save(null, ['validate' => false]);
		}

		// This will select the last address used in checkout as user's new default address.
		$result = $result && $user->save([$typeIdField => $address->id], [
			'whitelist' => [$typeIdField, 'id']
		]);
		$result = $result && $entity->save([$typeIdField => $address->id], [
			'whitelist' => [$typeIdField, 'id']
		]);
		if (!$result) {
			return false;
		}
		return $entity->{$typeField} = $address;
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

	// Should equal the invoice, once one is attached.
	public function totals($entity, $user) {
		$cart = $entity->cart();

		$result = $cart->totals($user);
		$result = $result->add($entity->shippingMethod()->price($user, $cart));
		$result = $result->add($entity->paymentMethod()->price($user, $cart));

		return $result;
	}

	public function generateShipment($entity, $user, $cart, array $data = []) {
		extract(Message::aliases());

		$shipment = Shipments::create([
			$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id,
			'status' => 'created',
			'method' => $entity->shipping_method,
			'note' => $t('Order No.', ['scope' => 'ecommerce_core']) . ': ' . $entity->number,
			'terms' => Settings::read('ecommerce.shipmentTerms')
		]);
		$shipment = $entity->address('shipping')->copy($shipment, 'address_');

		if (!$shipment->save()) {
			return false;
		}
		$entity->ecommerce_shipment_id = $shipment->id;

		foreach ($cart->positions() as $cartPosition) {
			$product = $cartPosition->product();

			$description  = $product->title . ' ';
			$description .= '(#' . $product->number . ')';

			$price = $cartPosition->product()->price($user);

			$shipmentPosition = ShipmentPositions::create([
				'ecommerce_shipment_id' => $shipment->id,
				'description' => $description,
				'quantity' => $cartPosition->quantity,
				'amount_rate' => $price->amount_rate,
				'amount_type' => $price->amount_type,
				'amount_currency' => $price->amount_currency,
				'amount' => $price->amount
			]);
			if (!$shipmentPosition->save(null, ['localize' => false])) {
				return false;
			}
		}

		// Shipping address is now contained in shipment.
		// For clarity nullify field.
		$entity->shipping_address_id = null;

		return $entity->save();
	}

	public function generateInvoice($entity, $user, $cart, array $data = []) {
		extract(Message::aliases());

		$group = ClientGroups::find('first', ['conditions' => compact('user')]);

		if (!$group) {
			return false;
		}

		$invoice = Invoices::create([
			$user->isVirtual() ? 'virtual_user_id' : 'user_id' => $user->id,
			'user_vat_reg_no' => $user->vat_reg_no,
			'tax_type' => $group->taxType,
			'tax_note' => $group->taxType()->note,
			'date' => date('Y-m-d'),
			'status' => 'awaiting-payment',
			'note' => $t('Order No.', ['scope' => 'ecommerce_core']) . ': ' . $entity->number,
			'terms' => Settings::read('billing.paymentTerms')
		]);
		$invoice = $user->address('billing')->copy($invoice, 'address_');

		if (!$invoice->save()) {
			return false;
		}
		if (!$entity->save(['billing_invoice_id' => $invoice->id])) {
			return false;
		}

		$currency = $invoice->currency;

		foreach ($cart->positions() as $cartPosition) {
			$product = $cartPosition->product();

			$description  = $product->title . ' ';
			$description .= '(#' . $product->number . ')';

			$price = $product->price($user);

			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $description,
				'quantity' => $cartPosition->quantity,
				'amount_rate' => $price->amount_rate,
				'amount_type' => $price->amount_type,
				'amount_currency' => $price->amount_currency,
				'amount' => $price->amount,
			]);
			if (!$invoicePosition->save(null, ['localize' => false])) {
				return false;
			}
		}

		$price = $entity->shippingMethod()->price($user, $cart);
		if ($price->getAmount()) {
			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $entity->shippingMethod()->title,
				'quantity' => 1,
				'amount_rate' => $price->getRate(),
				'amount_currency' => $price->getCurrency(),
				'amount_type' => $price->getType(),
				'amount' => $price->getAmount()
			]);
			if (!$invoicePosition->save(null, ['localize' => false])) {
				return false;
			}
		}
		$price = $entity->paymentMethod()->price($user, $cart);
		if ($price->getAmount()) {
			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $entity->paymentMethod()->title,
				'amount_rate' => $price->getRate(),
				'amount_currency' => $price->getCurrency(),
				'amount_type' => $price->getType(),
				'amount' => $price->getAmount()
			]);
			if (!$invoicePosition->save(null, ['localize' => false])) {
				return false;
			}
		}

		// Billing address is now contained in invoice.
		// For clarity nullify field.
		$entity->billing_invoice_address_id = null;

		return $entity->save() && $invoice->save(['is_locked' => true]);
	}

	// Retrieves either the shipping or billing address. If
	// a shipment or invoice is already associated with the order
	// their respective addresses will be returned.
	//
	// Please note that for BC we don't rely on i.e. `shipping_address_id`
	// being null to indicate that we should use the shipment's address.
	public function address($entity, $type) {
		if ($type == 'shipping') {
			if ($entity->ecommerce_shipment_id) {
				return $entity->shipment()->address();
			} else {
				return Addresses::find('first', ['conditions' => [
					'id' => $entity->shipping_address_id
				]]);
			}
		} elseif ($type == 'billing') {
			if ($entity->billing_invoice_id) {
				return $entity->invoice()->address();
			} else {
				return Addresses::find('first', ['conditions' => [
					'id' => $entity->billing_address_id
				]]);
			}
		}
		throw new Exception("Unknown address type `{$type}`.");
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

				if (($invoice && !$invoice->isCancelable()) || !$shipment->isCancelable()) {
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

				$order   = $entity;
				$user    = $order->user();
				$invoice = $entity->invoice();

				// Implicitly transfer cart reservation into shipment reservations.
				// Once an order is checked-out reservations are determined by shipment.

				if (!$user->is_notified) {
					return true;
				}
				return Mailer::deliver('order_checked_out', [
					'library' => 'ecommerce_core',
					'to' => $user->email,
					'bcc' => $contact['email'],
					'subject' => $t('Your order #{:number}.', [
						'locale' => $user->locale,
						'scope' => 'ecommerce_core',
						'number' => $order->number
					]),
					'data' => [
						'user' => $user,
						'order' => $order,
						// Orders without invoices are OK. But then we do
						// pass an empty invoice into the template. The template
						// will then have to deal with that. Also see conditional
						// attachment below.
						'invoice' => $invoice ?: null
					],
					'attach' => !$invoice ? [] : [
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

	/* Deprecated */

	public function totalAmount($entity, $user) {
		throw new Exception('Orders::totalAmount is deprecated in favor of totals.');
	}

	public function totalTax($entity, $user, $cart) {
		throw new Exception('Orders::totalTax is deprecated.');
	}

}

Orders::init();

?>