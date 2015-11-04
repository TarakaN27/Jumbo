<?php
namespace common\components\customComponents\ActionColumnSettings;
use Yii;
use yii\helpers\Html;
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 4.11.15
 * Time: 13.09
 */
class ActionColumnSettings extends \yii\grid\Column
{
	public
		$droupClass = 'btn-group',
		$ulOptions = ['class' => 'dropdown-menu'],
		$links = [],
		$params = ['id' => 'id'],
		$label = NULL;



	public function init()
	{
		parent::init();
		if(!$this->label)
			$this->label =  Yii::t('app/common','Settings');
	}

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		$items = [];
		foreach($this->links as $item) {
			$url = [$item['url']];
			foreach($this->params as $key => $param)
				$url[$key] = $model->$param;
			$items[] = Html::a($item['title'],$url, isset($item['options']) ? $item['options'] : []);
		}

		return  '<div class="'.$this->droupClass.'">
                      <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false">
		'.$this->label.' <span class="caret"></span>
                </button>'.Html::decode(Html::ul($items,$this->ulOptions)).
		'</div>';
	}

}