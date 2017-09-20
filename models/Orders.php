<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace ecommerce_core\models;

use DateTime;
use Exception;
use lithium\g11n\Message;
use lithium\analysis\Logger;
use lithium\util\Validator;

use base_address\models\Addresses;
use base_core\extensions\cms\Features;
use base_core\extensions\cms\Settings;
use billing_invoice\models\InvoicePositions;
use billing_invoice\models\Invoices;
use ecommerce_core\models\Carts;
use ecommerce_core\models\Shipments;
use billing_payment\billing\payment\Methods as PaymentMethods;
use ecommerce_core\ecommerce\shipping\Methods as ShippingMethods;
use ecommerce_core\models\Products;

class Orders extends \base_core\models\Base {

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
		'User' => [
			'to' => 'base_core\models\Users',
			'key' => 'user_id'
		],
		'Invoice' => [
			'to' => 'billing_invoice\models\Invoices',
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

	protected $_actsAs = [
		'base_core\extensions\data\behavior\RelationsPlus',
		'base_core\extensions\data\behavior\Timestamp',
		'base_core\extensions\data\behavior\Uuid',
		'base_core\extensions\data\behavior\ReferenceNumber',
		'base_core\extensions\data\behavior\StatusChange',
		'base_core\extensions\data\behavior\Searchable' => [
			'fields' => [
				'User.name',
				'User.number',
				'shipping_method',
				'payment_method',
				'modified',
				'number',
				'Invoice.number',
				'Shipment.number',
				'status',
				'Invoice.status',
				'Shipment.status'
			]
		]
	];

	public static function init() {
		$model = static::_object();
		extract(Message::aliases());

		static::behavior('base_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('order.number')
		);

		Validator::add('Orders.checked', function($value, $format, $options) {
			return $value === '1' || $value === true;
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
				'Orders.checked',
				'on' => ['checkoutConfirm'],
				'message' => $t('You must accept the terms.', ['scope' => 'ecommerce_core'])
			]
		];

		if (!static::behavior('ReferenceNumber')->config('generate')) {
			$model->validates['number'] = [
				'notEmpty' => [
					'notEmpty',
					'on' => ['create', 'update'],
					'last' => true,
					'message' => $t('This field cannot be empty.', ['scope' => 'ecommerce_core'])
				],
				'isUnique' => [
					'isUniqueReferenceNumber',
					'on' => ['create', 'update'],
					'message' => $t('This number is already in use.', ['scope' => 'ecommerce_core'])
				]
			];
		}
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
				'user_id' => $user->id
			] + $address->data()
		]);
		if ($exists) {
			$address = $exists; // Need id for setting default address later.
			$result = true;
		} else {
			$address = Addresses::create([
				'user_id' => $user->id
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
		return PaymentMethods::registry($entity->payment_method);
	}

	public function shippingMethod($entity) {
		return ShippingMethods::registry($entity->shipping_method);
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

		$shipment = Shipments::create($data + [
			'user_id' => $user->id,
			'status' => 'created',
			'method' => $entity->shipping_method,
			'note' => $t('Order No.', ['scope' => 'ecommerce_core']) . ': ' . $entity->number
		]);
		$shipment = $entity->address('shipping')->copy($shipment, 'address_');

		if (!$shipment->save()) {
			return false;
		}
		$entity->ecommerce_shipment_id = $shipment->id;

		foreach ($cart->positions() as $cartPosition) {
			if (Products::hasBehavior('Translatable')) {
				$product = $cartPosition->product(['translate' => $user->locale]);
			} else {
				$product = $cartPosition->product();
			}

			$description  = $product->title . ' ';
			$description .= '(#' . $product->number . ')';

			$price = $cartPosition->product()->price($user, $cartPosition->method);

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

		$invoice = Invoices::create($data + [
			'user_id' => $user->id,
			'status' => 'created',
			'method' => $entity->payment_method,
			'note' => $t('Order No.', ['scope' => 'ecommerce_core']) . ': ' . $entity->number
		]);
		if (!$invoice->save()) {
			return false;
		}
		if (!$entity->save(['billing_invoice_id' => $invoice->id])) {
			return false;
		}

		foreach ($cart->positions() as $cartPosition) {
			if (Products::hasBehavior('Translatable')) {
				$product = $cartPosition->product(['translate' => $user->locale]);
			} else {
				$product = $cartPosition->product();
			}

			$description  = $product->title . ' ';
			$description .= '(#' . $product->number . ')';

			$price = $product->price($user, $cartPosition->method);

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
				'description' => $entity->shippingMethod()->title(),
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
				'description' => $entity->paymentMethod()->title(),
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
		$entity->billing_address_id = null;

		return $entity->save();
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
		if ($entity->status !== 'checking-out') {
			return false;
		}
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->modified);
		return strtotime(Settings::read('checkout.expire'), $date->getTimestamp()) < time();
	}

	public function statusChange($entity, $from, $to) {
		extract(Message::aliases());

		switch ($to) {
			case 'cancelled':
				// Relies on transactional rollback on error.

				if ($invoice = $entity->invoice()) {
					if (!$invoice->isCancelable()) {
						return false;
					}
					$result = $invoice->save(['status' => 'cancelled'], [
						'whitelist' => ['status'],
						'validate' => false
					]);
					if (!$result) {
						return false;
					}
				}
				if ($shipment = $entity->shipment()) {
					if (!$shipment->isCancelable()) {
						return false;
					}
					$result = $shipment->save(['status' => 'cancelled'], [
						'whitelist' => ['status'],
						'validate' => false
					]);
					if (!$result) {
						return false;
					}
				}
				return true;

			case 'checked-out':
				// Implicitly transfer cart reservation into shipment reservations.
				// Once an order is checked-out reservations are determined by shipment.
				break;
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