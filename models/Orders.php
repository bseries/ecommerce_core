<?php
/**
 * Bureau eCommerce
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_ecommerce\models;

use cms_ecommerce\models\Carts;
use cms_ecommerce\models\Shipments;
use cms_ecommerce\models\PaymentMethods;
use cms_ecommerce\models\ShippingMethods;
use cms_core\models\Users;
use cms_core\models\VirtualUsers;
use cms_billing\models\InvoicePositions;
use cms_billing\models\Invoices;
use cms_core\models\Addresses;
use cms_core\extensions\cms\Settings;
use DateTime;

class Orders extends \cms_core\models\Base {

	public static $enum = [
		'status' => [
			'checking-out',
			'checked-out',
			'cancelled-by-re-checkout'
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
			'class' => 'cms_ecommerce\models\Shipment',
			'key' => 'ecommerce_shipment_id'
		]
	];

	protected static $_actsAs = [
		'cms_core\extensions\data\behavior\Timestamp'
	];

	public static function nextNumber() {
		$pattern = Settings::read('orderNumberPattern');

		$item = static::find('first', [
			'conditions' => $a = [
				'number' => [
					'LIKE' => strftime($pattern['prefix']) . '%'
				]
			],
			'order' => ['number' => 'DESC'],
			'fields' => ['number']
		]);
		if ($item && ($number = $item->number)) {
			$number++;
		} else {
			$number = strftime($pattern['prefix']) . sprintf($pattern['number'], 1);
		}
		return $number;
	}

	public function shipment($entity) {
		return Shipments::findById($entity->shipment_id);
	}

	public function user($entity) {
		if ($entity->user_id) {
			return Users::findById($entity->user_id);
		}
		return VirtualUsers::findById($entity->virtual_user_id);
	}

	public function cart($entity) {
		return Carts::find('first', [
			'conditions' => [
				'id' => $entity->ecommerce_cart_id
			]
		]);
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

	public function totalAmount($entity, $user, $cart, $type, $taxZone, $currency) {
		$result = $this->cart($entity)->totalAmount($user, $type, $taxZone, $currency);

		$result = $result->add($this->shippingMethod($entity)->price($user, $cart, $type, $taxZone, $currency));
		$result = $result->add($this->paymentMethod($entity)->price($user, $cart, $type, $taxZone, $currency));

		return $result;
	}

	public function totalTax($entity, $user, $cart, $taxZone, $currency) {
		$result = $this->totalAmount($entity, $user, $cart, 'gross', $taxZone, $currency);
		$result = $result->subtract($this->totalAmount($entity, $user, $cart, 'net', $taxZone, $currency));

		return $result;
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

	public function generateInvoice($entity, $user, $cart) {
		$invoice = Invoices::createForUser($user);
		$data = [
			'date' => date('Y-m-d'),
			'status' => 'created',
			'currency' => 'EUR'
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

			$description  = $cartPosition->quantity . ' x ';
			$description .= $product->title . ' ';
			$description .= '(#' . $product->number . ')';

			$invoicePosition = InvoicePositions::create([
				'billing_invoice_id' => $invoice->id,
				'description' => $description,
				'currency' => $currency,
				'total_gross' => $cartPosition->totalAmount($user, 'gross', $taxZone, $currency)->getAmount(),
				'total_net' => $cartPosition->totalAmount($user, 'net', $taxZone, $currency)->getAmount(),
			]);
			if (!$invoicePosition->save()) {
				return false;
			}
		}
		$invoicePosition = InvoicePositions::create([
			'billing_invoice_id' => $invoice->id,
			'description' => $entity->shippingMethod($entity)->title,
			'currency' => $currency,
			'total_gross' => $entity->shippingMethod($entity)->price($user, $cart, 'gross', $taxZone, $currency)->getAmount(),
			'total_net' => $entity->shippingMethod($entity)->price($user, $cart, 'net', $taxZone, $currency)->getAmount()
		]);
		if (!$invoicePosition->save()) {
			return false;
		}

		$invoicePosition = InvoicePositions::create([
			'billing_invoice_id' => $invoice->id,
			'description' => $entity->paymentMethod($entity)->title,
			'currency' => $currency,
			'total_gross' => $entity->paymentMethod($entity)->price($user, $cart, 'gross', $taxZone, $currency)->getAmount(),
			'total_net' => $entity->paymentMethod($entity)->price($user, $cart, 'net', $taxZone, $currency)->getAmount()
		]);
		if (!$invoicePosition->save()) {
			return false;
		}

		if (!$invoice->save(['is_locked' => true])) {
			return false;
		}
		return true;
	}

	public function address($entity, $type) {
		$field = $type . '_address_id';
		return Addresses::findById($entity->$field);
	}
}

Orders::applyFilter('create', function($self, $params, $chain) {
	static $useFilter = true;

	$entity = $chain->next($self, $params, $chain);

	if (!$useFilter) {
		return $entity;
	}

	if (!$entity->exists()) {
		$useFilter = false;
		$entity->number = Orders::nextNumber();
		$useFilter = true;
	}
	return $entity;
});

?>