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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace ecommerce_core\config;

use base_core\extensions\cms\Settings;
use ecommerce_core\models\Orders;
use ecommerce_core\models\Shipments;
use li3_mailer\action\Mailer;
use lithium\aop\Filters;
use lithium\g11n\Message;

if (Settings::read('order.sendCheckedOutMail')) {
	Filters::apply(Orders::class, 'statusChange', function($params, $next) {
		extract(Message::aliases());

		if ($params['to'] !== 'checked-out') {
			return true;
		}
		$contact = Settings::read('contact.billing');

		$order   = $params['entity'];
		$user    = $order->user();
		$invoice = $params['entity']->invoice();

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
	});
}

if (Settings::read('shipment.sendShippedMail')) {
	Filters::apply(Shipments::class, 'statusChange', function($params, $next) {
		extract(Message::aliases());

		if ($params['to'] !== 'shipped') {
			return true;
		}
		$user = $params['entity']->user();
		$order = $params['entity']->order();

		if (!$user->is_notified) {
			return true;
		}
		if ($order) {
			$subject = $t('Order #{:number} shipped.', [
				'locale' => $user->locale,
				'scope' => 'ecommerce_core',
				'number' => $order->number
			]);
		} else {
			$subject = $t('Shipment #{:number} shipped.', [
				'locale' => $user->locale,
				'scope' => 'ecommerce_core',
				'number' => $params['entity']->number
			]);
		}
		return Mailer::deliver('shipment_shipped', [
			'library' => 'ecommerce_core',
			'to' => $user->email,
			'subject' => $subject,
			'data' => [
				'user' => $user,
				'order' => $order ?: null,
				'shipment' => $params['entity']
			]
		]);
	});
}


?>
