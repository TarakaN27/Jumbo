<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.6.16
 * Time: 11.27
 */

namespace common\components\rabbitmq;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;

class Rabbit extends Component
{
    public
        $host = '127.0.0.1',
        $port = '5672',
        $login = 'guest',
        $password = 'guest',
        $vhost = '/';

    protected
        $connect =  NULL;

    CONST
        QUEUE_ACTS_SEND_LETTER = 'act_send_letter';         //очередь для отправки писем по актам

    /**
     * @return array
     */
    public static function getQueueMap()
    {
        return [
            self::QUEUE_ACTS_SEND_LETTER => 'Очередь для отправки актов по почте'
        ];
    }

    /**
     * @param $name
     * @return bool
     */
    public static function isAllowQueue($name)
    {
        $arQueues = self::getQueueMap();
        return in_array($name,$arQueues);
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed
     */
    public static function decodeMessage(AMQPMessage $msg)
    {
        return Json::decode($msg->body);
    }

    /**
     *
     */
    public function init()
    {
        parent::init();
    }

    /**
     * close connection
     */
    public function __destruct()
    {
        if(!is_null($this->connect))
            $this->connect->close();
    }

    /**
     *  Инициализируем подключение
     */
    protected function initConnection()
    {
        $this->connect = new AMQPStreamConnection($this->host,$this->port,$this->login,$this->password,$this->vhost);
    }

    /**
     * @param string $queueName
     * @param $msgString
     * @param string $exchange
     * @return bool
     * Отправка сообщения
     */
    public function sendMessage($queueName,$msgString,$exchange = '')
    {
        try{
            $channel = $this->getChannel($queueName,$exchange);         //инициализируем канал
            $msg = $this->prepareMessage($msgString,['content_type' => 'text/plain','delivery_mode' => 2 ]);    //формируем сообщение
            $channel->basic_publish($msg, $exchange, $queueName);       //публикуем сообщение(сообщение,обмен,ключ маршрутизации(очередь))
            $channel->close();                                          //закрываем канал
            return TRUE;
        }catch (\Exception $e)
        {
            //@todo залогировать ошибку
        }
        return FALSE;
    }


    /**
     * Слушатель
     * не отправляем новое сообщение на обработчик, пока он
     * не обработал и не подтвердил предыдущее. Вместо этого
     * направляем сообщение на любой свободный обработчик
     * $channel->basic_qos(
     *      null,   //размер предварительной выборки - размер окна предварительнйо выборки в октетах, null означает “без определённого ограничения”
     *      1,  	//количество предварительных выборок - окна предварительных выборок в рамках целого сообщения
     *      null	//глобальный - global=null означает, что настройки QoS должны применяться для получателей, global=true означает, что настройки QoS должны применяться к каналу
     * );
     * * оповещает о своей заинтересованности в получении
     * сообщений из определённой очереди. В таком случае мы
     * говорим, что они регистрируют получателя, или устанавливают
     * подписку на очередь. Каждый получатель (подписка) имеет
     * идентификатор, называемый “тег получателя”.
     * $channel->basic_consume(
     *     'invoice_queue',    	#очередь
     *      '',                  #тег получателя - Идентификатор получателя, валидный в пределах текущего канала. Просто строка
     *      false,               #не локальный - TRUE: сервер не будет отправлять сообщения соединениям, которые сам опубликовал
     *      false,               #без подтверждения - false: подтверждения включены, true - подтверждения отключены. отправлять соответствующее подтверждение обработчику, как только задача будет выполнена
     *      false,                 #эксклюзивная - к очереди можно получить доступ только в рамках текущего соединения
     *      false,                 #не ждать - TRUE: сервер не будет отвечать методу. Клиент не должен ждать ответа
     *      array($this, 'process')	#функция обратного вызова - метод, который будет принимать сообщение
     *  );
     * @param $queueName
     * @param $callback
     * @throws \Exception
     */
    public function listener($queueName,$callback)
    {
        $channel = $this->getChannel($queueName);
        if(empty($channel))
            throw new \Exception('AMQP channel is NULL');
        if(empty($callback))
            throw new \Exception('AMQP callback is empty');

        $channel->basic_qos(null,1,null);

        $channel->basic_consume($queueName, '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->connect->close();
        //return true;
    }

    /**
     * Get chanel
     * @param $queueName
     * @return AMQPChannel
     * @throws Exception
     */
    protected function getChannel($queueName,$exchange = '')
    {
        if(is_null($this->connect))
        {
            $this->initConnection();                                //инициализруем подключение
        }

        if(empty($queueName) || is_null($this->connect) || strlen($queueName) > 255)
            throw new Exception('AMQP connection is NULL OR queue name is invalid');

        /** @var AMQPChannel $channel */
        $channel = $this->connect->channel();
        $channel->queue_declare(
            $queueName,	    //queue name - Имя очереди может содержать до 255 байт UTF-8 символов
            false,      	//passive - может использоваться для проверки того, инициирован ли обмен, без того, чтобы изменять состояние сервера
            true,      	    //durable - убедимся, что RabbitMQ никогда не потеряет очередь при падении - очередь переживёт перезагрузку брокера
            false,      	//exclusive - используется только одним соединением, и очередь будет удалена при закрытии соединения
            false       	//autodelete - очередь удаляется, когда отписывается последний подписчик
        );

        return $channel;
    }

    /**
     * @param $message
     * @param null $properties
     * @return AMQPMessage
     * @throws \Exception
     * Подготовка сообщения
     */
    protected function prepareMessage($message, $properties = null)
    {
        if (empty($message)) {
            throw new \Exception('AMQP message can not be empty');
        }
        if (is_array($message) || is_object($message)) {
            $message = Json::encode($message);
        }
        return new AMQPMessage($message, $properties);
    }
}