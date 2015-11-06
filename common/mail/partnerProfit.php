<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 5.11.15
 */
?>
<?php echo Yii::t('app/common','Partner profit')?>:</br>
<?php foreach($arData as $key => $data)
{
	echo Yii::t('app/common',$key).' : '.$data;
}
