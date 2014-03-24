<?php
/**
 * Magasin Core
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
use cms_core\extensions\cms\Settings;
use DateTime;

class Orders extends \cms_core\models\Base {

	public static $enum = [
		'status' => [
			'checking-out',
			'checked-out',
			'expired'
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
		'cms_core\extensions\data\behavior\ReferenceNumber'
	];

	public static function init() {
		$model = static::_object();

		static::behavior('cms_core\extensions\data\behavior\ReferenceNumber')->config(
			Settings::read('order.number')
		);
	}

	public function shipment($entity) {
		return Shipments::findById($entity->ecommerce_shipment_id);
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

	public function totalAmount($entity, $user, $cart, $taxZone) {
		$result = $entity->cart()->totalAmount($user, $taxZone);

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

	public function generateInvoice($entity, $user, $cart) {
		$invoice = Invoices::createForUser($user);
		$data = [
			'date' => date('Y-m-d'),
			'status' => 'created',
			'total_currency' => 'EUR'
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
		$price = $entity->paymentMethod()->price($user, $cart, $taxZone);
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

		if (!$invoice->save(['is_locked' => true])) {
			return false;
		}
		return true;
	}

	public function address($entity, $type) {
		$field = $type . '_address_id';
		return Addresses::findById($entity->$field);
	}

	public function isExpired($entity) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $entity->modified);
		return strtotime(Settings::read('checkout.expire'), $date->getTimestamp()) < time();
	}
}

Orders::init();

?>