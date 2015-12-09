<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.12.15
 * Time: 12.55
 */

namespace common\components\entityFields\widget;


use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\DetailView;

class EntityFieldsDisplayWidget extends Widget
{
	public
		$title = '',
		$options = ['class' => 'table table-striped table-bordered detail-view'],
		$model;

	protected
		$data;

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function run()
	{
		$str = '';
		if($this->title)
			$str.=Html::tag('h4',$this->title);

		$this->data = $this->model->getDisplayEntityValues();
		$str.= DetailView::widget([
			'model' => $this->data,
			'options' => $this->options,
			'attributes' => $this->attributesForDetailView()
		]);
		return $str;
	}

	/**
	 * @return array
	 */
	protected function attributesForDetailView()
	{
		$arResult = [];
		if(is_array($this->data))
			foreach($this->data as $data)
				$arResult [] = [
					'label' => $data['name'],
					'value' => $data['value']
				];
		return $arResult;
	}




}