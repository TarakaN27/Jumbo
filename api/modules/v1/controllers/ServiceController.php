<?php
namespace api\modules\v1\controllers;
use api\components\AbstractActiveActionREST;
use common\components\helpers\CustomHelper;
use common\models\Bills;
use common\models\BillTemplate;
use common\models\CUser;
use common\models\CuserExternalAccount;
use common\models\CuserPreferPayCond;
use common\models\LegalPerson;
use common\models\PaymentCondition;
use common\models\Services;
use Faker\Provider\nl_NL\Payment;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 30.9.15
 * Time: 16.51
 */
class ServiceController extends AbstractActiveActionREST
{
    public $modelClass = 'common\models\Services';

    CONST
        USER_REQUEST_LIMIT = 50;

    public function actions()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function verbs()
    {
        $tmp = parent::verbs();
        return ArrayHelper::merge($tmp,[
            'get-services' => ['POST']
        ]);
    }

    /**
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGetServices()
    {
        $this->checkAccessByToken();
        $rawData = file_get_contents('php://input'); //получаем сырые данные. Должна передобавться Json-Строка
        $rawData = Json::decode($rawData);
        if(empty($rawData) || !isset($rawData['users']) || empty($rawData['users']))
            return ['error' => 'не задан обязательный параметр users'];
        $rawUsers = $rawData['users'];  //запрашиваемые пользователи(SK - secret key)
        if(count($rawUsers) > self::USER_REQUEST_LIMIT)
            return ['error' => 'Кол-во пользователей не может превышать 50'];

        $arUsers = CuserExternalAccount::findAllBySecretKeyCached($rawUsers);   //находим ID пользователей по SK

        if(empty($arUsers))
            return ['error' => 'Пользователи не найдены'];

        $arUIdsSc = ArrayHelper::map($arUsers,'cuser_id','secret_key'); //соберем соответсвие id пользователя => SK
        $arServ = Services::getServicesMap();   // получим все услуги
        $arPreferTMP = CuserPreferPayCond::getPreferCondForUsers(array_keys($arUIdsSc));    //получим услуги для которых заданы условия
        $arPreferCond = [];     //соберем по пользователям, услуги для которых заданы условия
        foreach($arPreferTMP as $pref)
        {
                $arPreferCond[$pref->cuser_id][$pref->service_id] = !empty($pref->cond_id);
        }

        $arReturn = []; //вернем массив с услугами для каждого пользователя
        $arUIdsSc = array_flip($arUIdsSc); //меняем местами CuserID и SK
        foreach($rawUsers as $user)
        {
            $arServReturn = [];     //соберм услиги для пользователя и разрешено ли ему выписывать счет
            $userID = isset($arUIdsSc[$user]) ? $arUIdsSc[$user] : NULL;
            $error = '';
            if(!is_null($userID))
            {
                foreach($arServ as $key => $value)
                {
                    $arServReturn [] = [
                        'id' => $key,
                        'name' => $value,
                        'allow' => isset($arPreferCond[$userID]) && isset($arPreferCond[$userID][$key]) ?
                            $arPreferCond[$userID][$key] : FALSE,
                        'minAmount' => Yii::$app->config->get('min_bill_amount')
                    ];
                }
            }else{
                $error = 'Пользователь не найден';
            }

            $arReturn [] = [
                'user' => $user,
                'error' => $error,
                'services' => $arServReturn
            ];
            unset($arServReturn,$userID,$error);
        }

        return $arReturn;
    }

    /**
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionGetNewBill()
    {
        $this->checkAccessByToken();
        $sk = Yii::$app->request->post('sk');
        $servID = Yii::$app->request->post('servID');
        $amount = Yii::$app->request->post('amount');

        if(!$sk || !$servID || !$amount || !is_numeric($amount))
            throw new InvalidParamException('secret key, servID, amount is required params');

        $obUser = CuserExternalAccount::findOneBySecretKeyCahed($sk);   //находим id пользователя по SK

        if(!$obUser)
            throw new NotFoundHttpException('User not found');

        if($obUser->contructor != CUser::CONTRACTOR_YES) //проверяем, чтобы пользователь был конрагентом
            throw new ForbiddenHttpException('Not allowed');

        $obPrefCond = CuserPreferPayCond::findOneByUserIDAndServiceID($obUser->id,$servID); // проверяем заданы ли условия для поьзователя
        if(!$obPrefCond)
            throw new NotFoundHttpException('prefer cond not found');

        $obCond = $obPrefCond->cond;    //получаем условие
        if(!$obCond)
            throw new NotFoundHttpException('Condition not found');
        /** @var LegalPerson $obLPerson */
        $obLPerson = LegalPerson::findOneByIDCached($obCond->l_person_id); //из условия получаем Юр.Адрес
        if(!$obLPerson)
            throw new NotFoundHttpException('legal person not found');

        /** @var BillTemplate $obBTpl */
        $obBTpl = BillTemplate::find()->where([ 'l_person_id' => $obLPerson->id,'service_id' => $servID])->one();   // по юр. адресу и id условия находим шаблон
        if(!$obBTpl)
            throw new NotFoundHttpException('Bill template not found');

        $obBill = new Bills();
        $obBill->manager_id = $obUser->manager_id;
        $obBill->cuser_id = $obUser->id;
        $obBill->l_person_id = $obLPerson->id;
        $obBill->service_id = $servID;
        $obBill->docx_tmpl_id = (int)$obLPerson->docx_id;    // шаблон документа
        $obBill->amount = $amount;
        $obBill->external = Bills::YES;
        $obBill->bill_template = $obBTpl->id;
        $obBill->use_vat = $obBTpl->use_vat;
        $obBill->vat_rate = CustomHelper::getVat();
        $obBill->description = $obBTpl->description;
        $obBill->object_text = $obBTpl->object_text;
        $obBill->buy_target = Yii::t('app/documents','DefaultBuytarget');
        if($obBill->save()) //если все ок. то вернем Secret key для документа. по нему потом пользователь сможет получить PDF
            return $obBill->bsk;

        throw new ServerErrorHttpException();
    }

}