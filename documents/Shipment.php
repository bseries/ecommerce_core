<?php
/**
 * eCommerce Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
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

namespace ecommerce_core\documents;

use IntlDateFormatter;
use lithium\g11n\Message;

class Shipment extends \billing_core\documents\BaseFinancial {

	protected $_layout = 'shipment';

	protected function _preparePage() {
		$backupHeight = $this->_currentHeight;
		$backup = $this->_margin;

		$this->_margin = [100, 33, 100, 33];
		$this->_currentHeight = 800;

		foreach (explode("\n", $this->_sender->address()->format('postal')) as $key => $line) {
			$this->_drawText($line, 'right', [
				'offsetY' => $key ? $this->_skipLines() : $this->_currentHeight
			]);
		}

		$this->_currentHeight = 90;

		$this->_margin = $backup;
		$this->_currentHeight = $backupHeight;
	}

	// 1.
	protected function _compileRecipientAddressField() {
		$this->_useStyle('gamma');

		foreach (explode("\n", $this->_recipient->address('shipping')->format('postal')) as $key => $line) {
			$this->_drawText($line, 'left', [
				'offsetY' => $key ? $this->_skipLines() : 685
			]);
		}
	}

	// 2.
	protected function _compileDateAndCity() {
		$this->_useStyle('gamma');

		extract(Message::aliases());

		$formatter = new IntlDateFormatter(
			$this->_recipient->locale,
			IntlDateFormatter::SHORT,
			IntlDateFormatter::NONE,
			$this->_recipient->timezone
		);

		$text = $t('{:city}, the {:date}', [
			'scope' => 'base_document',
			'locale' => $this->_recipient->locale,
			'city' => $this->_sender->address()->locality,
			'date' => $formatter->format($this->_entity->date())
		]);
		$this->_drawText($text, 'right', [
			'offsetY' => 560
		]);
	}

	// 3.
	protected function _compileType() {
		$this->_useStyle('beta--bold');

		$backup = $this->_margin;

		$this->_margin = [100, 33, 100, 33];

		$this->_drawText(strtoupper($this->_type), 'right', [
			'offsetY' => 680
		]);

		$this->_margin = $backup;
	}

	// 4.
	protected function _compileNumbers() {
		$this->_useStyle('gamma');

		extract(Message::aliases());

		$backup = $this->_margin;
		$this->_margin = [100, 33, 100, 33];

		$this->_drawText($t('{:number} — Client No.', [
			'scope' => 'base_document',
			'locale' => $this->_recipient->locale,
			'number' => $this->_recipient->number
		]), 'right', [
			'offsetY' => 661
		]);
		$this->_drawText($t('{:number} — Shipment No.', [
			'scope' => 'base_document',
			'locale' => $this->_recipient->locale,
			'number' => $this->_entity->number
		]),  'right', [
			'offsetY' => $this->_skipLines()
		]);

		$this->_margin = $backup;
	}

	// 5.
	protected function _compileSubject() {
		$this->_useStyle('gamma--bold');

		$this->_drawText($this->_subject, 'left', [
			'offsetY' => 540
		]);
	}

	// 6. No Hello
	protected function _compileHello() {}

	//  7. No Intro
	protected function _compileIntro() {}

	// 8.
	protected function _compileTableHeader() {
		$this->_useStyle('gamma--bold');

		extract(Message::aliases());

		$showNet = in_array($this->_recipient->role, ['merchant', 'admin']);
		$this->_currentHeight = 435;

		$this->_drawText($t('Description', [
			'scope' => 'base_document',
			'locale' => $this->_recipient->locale
		]), 'left', [
			'width' => 500,
			'offsetX' => $offsetX = 0
		]);
		$this->_drawText($t('Quantity', [
			'scope' => 'base_document',
			'locale' => $this->_recipient->locale
		]), 'right', [
			'width' => 100,
			'offsetX' => $offsetX += 500
		]);

		$this->_currentHeight = $this->_skipLines();

		$this->_drawHorizontalLine();
	}

	// 9.
	protected function _compileTablePosition($position) {
		$this->_useStyle('gamma');
		extract(Message::aliases());

		$this->_currentHeight = $this->_skipLines();

		$this->_drawText($position->description, 'left', [
			'width' => 500,
			'offsetX' => $offsetX = 0
		]);
		$this->_drawText((integer) $position->quantity, 'right', [
			'width' => 100,
			'offsetX' => $offsetX += 500
		]);

		// Page break; redraw costs table header.
		if ($this->_currentHeight <= 250) {
			$this->_nextPage();
			$this->_compileTableHeader();
		}
	}

	// 10.
	protected function _compileTableFooter() {
		$this->_useStyle('gamma');

		$this->_currentHeight = $this->_skipLines(3);
		$this->_drawHorizontalLine();

		$this->_currentHeight = $this->_skipLines(2.5);
		$this->_drawText($this->_entity->terms);

		$this->_currentHeight = $this->_skipLines(2);
		$this->_drawText($this->_entity->note);
	}
}

?>