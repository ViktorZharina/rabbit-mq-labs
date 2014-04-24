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
$channel->queue_declare('hello', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

/**
 * Объявляем callback для отображения входящего сообщения
 * 
 */
$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
};

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

$channel->basic_consume('hello', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
	echo count($channel->callbacks);
    $channel->wait();
}