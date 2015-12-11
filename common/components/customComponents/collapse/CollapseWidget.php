<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 15.34
 */

namespace common\components\customComponents\collapse;


use yii\bootstrap\Collapse;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class CollapseWidget extends Collapse
{
	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		Html::removeCssClass($this->options, ['widget' => 'panel-group']);
		Html::addCssClass($this->options, ['widget' => 'accordion']);
	}

	/**
	 * Renders collapsible items as specified on [[items]].
	 * @throws InvalidConfigException if label isn't specified
	 * @return string the rendering result
	 */
	public function renderItems()
	{
		$items = [];
		$index = 0;
		foreach ($this->items as $item) {
			if (!array_key_exists('label', $item)) {
				throw new InvalidConfigException("The 'label' option is required.");
			}
			$header = $item['label'];
			$options = ArrayHelper::getValue($item, 'options', []);
			Html::addCssClass($options, ['panel' => 'panel']);
			$items[] = Html::tag('div', $this->renderItem($header, $item, ++$index), $options);
		}

		return implode("\n", $items);
	}

}