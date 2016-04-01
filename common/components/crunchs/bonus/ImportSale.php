<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 31.3.16
 * Time: 16.41
 */

namespace common\components\crunchs\bonus;


use common\components\helpers\CustomHelper;
use common\models\Payments;
use common\models\PaymentsSale;

class ImportSale
{

	protected
		$delimiter = NULL,
		$_path  = [];

	/**
	 * @param array $arPath
	 */
	public function __construct(array $arPath,$delimiter = ';')
	{
		foreach($arPath as $path)
			$this->_path[] = \Yii::getAlias($path);
		$this->delimiter = $delimiter;
	}


	public function run()
	{
		foreach($this->_path as $filePath)
		{
			$arData = $this->loadFile($filePath);
			if(empty($arData))
				continue;

			$payID = [];
			foreach($arData as $data)
			{
				$payID [] = $data['paymentID'];
			}

			$arPaymentTmp = Payments::find()->where(['id' => $payID])->all();
			$arPayment = [];
			foreach($arPaymentTmp as $item)
			{
				$arPayment[$item->id] = $item;
			}

			foreach($arData as $data)
			{
				if(empty($data['saleManager']) || !isset($arPayment[$data['paymentID']]))
					continue;
				/** @var Payments $obPayment */
				$obPayment = $arPayment[$data['paymentID']];
				$obSale = new PaymentsSale([
					'cuser_id' => $obPayment->cuser_id,
					'service_id' => $obPayment->service_id,
					'buser_id' => $data['saleManager'],
					'sale_date' => $obPayment->pay_date,
					'created_at' => $obPayment->created_at,
					'updated_at' => $obPayment->updated_at,
					'sale_num' => 1
				]);
				$obSale->save();
			}
		}

		return TRUE;

	}

	/**
	 * @param $path
	 * @return array|bool
	 */
	protected function loadFile($path)
	{
		return CustomHelper::csv_to_array($path,$this->delimiter);
	}
}