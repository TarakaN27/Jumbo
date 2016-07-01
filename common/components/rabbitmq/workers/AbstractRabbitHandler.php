<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.6.16
 * Time: 12.22
 */

namespace common\components\rabbitmq\workers;


use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractRabbitHandler
{
    /**
     * @param AMQPMessage $msg
     */
    protected function messagesProcessed(AMQPMessage $msg)
    {
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }

}