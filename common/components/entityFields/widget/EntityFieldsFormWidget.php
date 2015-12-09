<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 8.12.15
 * Time: 14.55
 */

namespace common\components\entityFields\widget;


use yii\base\Widget;

class EntityFieldsFormWidget extends Widget
{
	public
		$form,
		$model;

	public function run()
	{
		$sReturn = '';
		$arFileds = $this->model->getEntityFields();
		$arValue = $this->model->getEntityFieldsValue();
		foreach($arFileds as $field)
			$sReturn.=$field->renderFormInput($this->form,$this->model);
		return $sReturn;
	}
}