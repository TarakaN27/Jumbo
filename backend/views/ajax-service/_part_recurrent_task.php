<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.5.16
 * Time: 18.11
 */
?>
<?php foreach ($models as $model):?>
<tr>
    <td>
        <?=$model->id;?>
    </td>
    <td>
        <?=\yii\helpers\Html::a($model->title,['/crm/task/view','id' => $model->id],['target' => '_blank']);?>
    </td>
</tr>
<?php endforeach;?>
