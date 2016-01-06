<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.1.16
 * Time: 10.56
 */

namespace backend\components\widgets\WorkDay;


use backend\components\widgets\WorkDay\assets\WorkDayAssets;
use common\models\WorkDay;
use yii\base\Widget;

class WorkDayWidget extends Widget
{

	public function run()
	{
		$begined = WorkDay::getBeginedDay(\Yii::$app->user->id);
		//var_dump($begined);
		if(!$begined) {
			$model = new WorkDay();

			$model->begin_time = date('Y-m-d H:i', time());
			$model->log_date = date('Y-m-d', time());
		}else{
			$model = clone $begined;
			$model->end_time = date('Y-m-d H:i', time());

			if(!empty($begined->end_time))
				$model->begin_time = date('Y-m-d H:i', time());
		}

		$this->registerAssets();
		return $this->render('work_day_widget',[
				'begined' => $begined,
				'model' => $model
			]
		);
	}

	protected function registerAssets()
	{
		$view = $this->getView();
		WorkDayAssets::register($view);
	}
}