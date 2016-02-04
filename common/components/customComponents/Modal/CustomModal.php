<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 4.2.16
 * Time: 17.23
 */

namespace common\components\customComponents\Modal;


use yii\bootstrap\Modal;
use yii\helpers\Html;

class CustomModal extends Modal
{
	/**
	 * Initializes the widget options.
	 * This method sets the default values for various options.
	 */
	protected function initOptions()
	{
		$this->options = array_merge([
			'class' => 'fade',
			'role' => 'dialog'
		], $this->options);
		Html::addCssClass($this->options, ['widget' => 'modal']);

		if ($this->clientOptions !== false) {
			$this->clientOptions = array_merge(['show' => false], $this->clientOptions);
		}

		if ($this->closeButton !== false) {
			$this->closeButton = array_merge([
				'data-dismiss' => 'modal',
				'aria-hidden' => 'true',
				'class' => 'close',
			], $this->closeButton);
		}

		if ($this->toggleButton !== false) {
			$this->toggleButton = array_merge([
				'data-toggle' => 'modal',
			], $this->toggleButton);
			if (!isset($this->toggleButton['data-target']) && !isset($this->toggleButton['href'])) {
				$this->toggleButton['data-target'] = '#' . $this->options['id'];
			}
		}
	}
}