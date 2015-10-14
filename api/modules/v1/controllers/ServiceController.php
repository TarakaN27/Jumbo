<?php
namespace api\modules\v1\controllers;
use api\components\AbstractActiveActionREST;
use common\models\CuserExternalAccount;
use common\models\CuserPreferPayCond;
use common\models\Services;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

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
                            $arPreferCond[$userID][$key] : FALSE
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

}