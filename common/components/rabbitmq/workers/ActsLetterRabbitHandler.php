<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.6.16
 * Time: 12.20
 */

namespace common\components\rabbitmq\workers;

use common\components\notification\TabledNotification;
use common\models\Acts;
use PhpAmqpLib\Message\AMQPMessage;
use common\components\rabbitmq\Rabbit;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class ActsLetterRabbitHandler extends AbstractRabbitHandler
{
    
    protected 
        $obMailer = NULL;
    
    /**
     * @param AMQPMessage $msg
     * @return bool
     */
    public function processing(AMQPMessage $msg)
    {
        $params = Rabbit::decodeMessage($msg); //декодируем сообщение
        if(!is_array($params) || !isset($params['iActId'],$params['toEmail'],$params['iBUserId']))
        {
            if(isset($params['iBUserId']) && is_numeric($params['iBUserId'])) {
                $errorText = 'Ошибка отправки акта '.isset($params['iActId']) ? $params['iActId'] : 'N/A';
                $this->addError((int)$params['iBUserId'], $errorText);
            }
            $this->messagesProcessed($msg);
            return TRUE;
        }
        $iActId = (int)$params['iActId'];
        $iBUSerId = (int)$params['iBUserId'];
        $toEmail = trim($params['toEmail']);

        /** @var Acts $obAct */
        $obAct = Acts::find()->where(['id' => $iActId])->with('legalPerson')->andWhere('sent is NULL OR sent = 0')->one();
        $tmpType = ArrayHelper::getValue($obAct,'legalPerson.letter_tpl_type');
        $tmpType = $tmpType > 0 ? '-'.$tmpType : '';

        if(!$obAct || !file_exists($obAct->getDocumentPath()))
        {
            $errorText = 'Ошибка отправки акта '.$iActId.' . Акт не найден или не существует pdf файл акта';
            $this->addError($iBUSerId, $errorText);
            $this->messagesProcessed($msg);
            return TRUE;
        }

        if(!$this->sendMail($toEmail,$obAct->getDocumentPath(),$tmpType))
        {
            $errorText = 'Ошибка отправки акта '.$iActId.' . Не удалось отправить письмо';
            $this->addError($iBUSerId, $errorText);
            $this->messagesProcessed($msg);
            return TRUE;
        }

        $obAct->sent = Acts::YES;
        if(!$obAct->save())
        {
            $errorText = 'Ошибка отправки акта '.$iActId.' . Письмо отправлено, но не удлось изменить статус акта в JUMBO';
            $this->addError($iBUSerId, $errorText);
            $this->messagesProcessed($msg);
            return TRUE;
        }

        $this->addSuccess($iBUSerId,'Акт '.$iActId.' успешно отправлен на емаил '.$toEmail);
        $this->messagesProcessed($msg);
        return FALSE;
    }

    /**
     * @param $toEmail
     * @param $documentPath
     * @return bool
     */
    protected function sendMail($toEmail,$documentPath,$tplType = '')
    {
        try {
            if($tplType == 2)
                return \Yii::$app->salesMailerSoft->compose( // отправялем уведомление по ссылке
                        [                           //указывам шаблон
                            'html' => 'actNotification-html'.$tplType,
                            'text' => 'actNotification-text'.$tplType
                        ]
                    )
                    ->setFrom([\Yii::$app->params['salesEmailSoft'] => \Yii::$app->params['salesEmailSoft']])    //от кого уходит письмо
                    ->setTo($toEmail)                                                                   //кому
                    //->setTo('motuzdev@gmail.com')
                    ->setBcc(\Yii::$app->params['salesEmailSoft'])                                          //скрытая копия
                    ->setSubject(\Yii::$app->params['actLetterSubjectSoft'])                                //тема письма
                    ->attach($documentPath)                                                             //прикрепляем акт к письму
                    ->send();
            else
                return \Yii::$app->salesMailer->compose( // отправялем уведомление по ссылке
                    [                           //указывам шаблон
                        'html' => 'actNotification-html'.$tplType,
                        'text' => 'actNotification-text'.$tplType
                    ]
                )
                    ->setFrom([\Yii::$app->params['salesEmail'] => \Yii::$app->params['salesName']])    //от кого уходит письмо
                    ->setTo($toEmail)                                                                   //кому
                    //->setTo('motuzdev@gmail.com')
                    ->setBcc(\Yii::$app->params['salesEmail'])                                          //скрытая копия
                    ->setSubject(\Yii::$app->params['actLetterSubject'])                                //тема письма
                    ->attach($documentPath)                                                             //прикрепляем акт к письму
                    ->send();
            }catch (Exception $e)
        {
            return FALSE;
        }
    }

    /**
     * @param $iUserId
     * @param $text
     */
    protected function addError($iUserId,$text)
    {
        TabledNotification::addMessage(
            'Отправка акта клиенту',
            $text,
            TabledNotification::TYPE_PRIVATE,
            TabledNotification::NOTIF_TYPE_ERROR,
            [$iUserId]
        );
    }

    /**
     * @param $iUserId
     * @param $text
     */
    protected function addSuccess($iUserId,$text)
    {
        TabledNotification::addMessage(
            'Отправка акта клиенту',
            $text,
            TabledNotification::TYPE_PRIVATE,
            TabledNotification::NOTIF_TYPE_SUCCESS,
            [$iUserId]
        );
    }
}