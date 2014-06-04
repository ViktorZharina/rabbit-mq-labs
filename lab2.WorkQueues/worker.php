<?php
/*---------------------------------------------------------------------------
 * @Project: rabbit-mq-labs
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * based on: https://www.rabbitmq.com/tutorials/tutorial-one-php.html
 */
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Создаем соединение.
 * 1. хост
 * 2. порт
 * 3. логин
 * 4. пароль
 * @var AMQPConnection
 */
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
/**
 * Запрашивает канал или создаем новый (в нашем случае)
 */
$channel = $connection->channel();

/**
 * Создаем самую простую очередь hello. 
 * 1. имя очереди, 
 * 2. passive - проверять существование очереди с таким именем. false - нет.
 * 3. durable - очередь продолжит работу даже после перезагрузки. false - нет.
 * 4. exclusive - определяет принадлежность очереди к текущему соединение. Соединение закрыто, очередь удаляется. false - нет.
 * 5. autodelete - если из очереди все сообщения были прочитаны, то она автоматически.
 * сообщения из очереди после приема.
 */
$channel->queue_declare('task_queue', false, false, false, false);
echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

/**
 * Объявляем callback для отображения входящего сообщения
 * 
 */
$callback = function($msg){
  echo " [x] Received ", $msg->body, "\n";
  sleep(substr_count($msg->body, '.'));
  echo " [x] Done", "\n";
  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

/**
 *1. prefetch-size
 *
 * The client can request that messages be sent in advance so that when the client finishes processing a message, the following * message is already held locally, rather than needing to be sent down the channel. Prefetching gives a performance 
 * improvement. This field specifies the prefetch window size in octets. The server will send a message in advance if it is 
 * equal to or smaller in size than the available prefetch size (and also falls into other prefetch limits). May be set to zero, * meaning "no specific limit", although other prefetch limits may still apply. The prefetch-size is ignored if the no-ack
 * option is set.
 *
 * The server MUST ignore this setting when the client is not processing any messages - i.e. the prefetch size does not limit the transfer of single messages to a client, only the sending in advance of more messages while the client still has one or more unacknowledged messages. 
 *
 * short prefetch-count
 * 
 * Specifies a prefetch window in terms of whole messages. This field may be used in combination with the prefetch-size field; a * message will only be sent in advance if both prefetch windows (and those at the channel and connection level) allow it. The 
 * prefetch-count is ignored if the no-ack option is set.
 *
 * The server may send less data in advance than allowed by the client's specified prefetch windows but it MUST NOT send more. 
 *
 * bit global
 *
 * RabbitMQ has reinterpreted this field. The original specification said: "By default the QoS settings apply to the current
 * channel only. If this field is set, they are applied to the entire connection." Instead, RabbitMQ takes global=false to mean
 * that the QoS settings should apply per-consumer (for new consumers on the channel; existing ones being unaffected) and
 * global=true to mean that the QoS settings should apply per-channel. 
 */
$channel->basic_qos(null, 1, null);

/**
 * Подписываемя на получение сообщения
 * 1. queue: имя очереди
 * 2. consumer_tag: ид получателя
 * 3. no_local:  не отправлять сообщения данному получателю на данном канале
 * 4. no_ack: true - подтверждать сообщение однажды, false - каждый раз
 * 5. exclusive: true, если это эсклюзивный получатель
 * 6. nowait: true.  Не ждать ответа от сервера. Сервер выкинет исключение в случае ошибки.
 * 7. PHP Callback
 */		

$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
	echo count($channel->callbacks);
    $channel->wait();
}