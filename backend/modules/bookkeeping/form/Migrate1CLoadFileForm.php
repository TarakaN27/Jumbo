<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 11.19
 */

namespace backend\modules\bookkeeping\form;


use common\components\acts\ActsDocumentsV2;
use common\components\behavior\UploadBehavior;
use common\components\customComponents\validation\ValidNumber;
use common\components\helpers\CustomHelper;
use common\models\ActImplicitPayment;
use common\models\Acts;
use common\models\ActServices;
use common\models\ActToPayments;
use common\models\Payments;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\widgets\ActiveForm;

class Migrate1CLoadFileForm extends Model
{
    public $src;                //Контрагент

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['src'], 'required'],
            [['src'],'file'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'src' => Yii::t('app/book','src'),
        ];
    }
}