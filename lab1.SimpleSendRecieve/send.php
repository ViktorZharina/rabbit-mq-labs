<?php
/*---------------------------------------------------------------------------
 * @Project: rabbit-mq-labs
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * based on: https://www.rabbitmq.com/tutorials/tutorial-one-php.html
 */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Создаем соединение. По сути AMQPConnection это обертка над socket 
 * 1. хост
 * 2. порт
 * 3. логин
 * 4. пароль
 * @var AMQPConnection
 */
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
/**
 * Запрашивает канал иил создаем новый (в нашем случае)
 */
$channel = $connection->channel();

/**
 * Создаем самую простую очередь hello. 
 * 1. имя очереди, 
 * 2. passive - проверять существование очереди с таким именем. false - нет.
 * 3. durable - обмен будет продолжен, даже после перезагрузки. false - нет.
 * 4. exclusive - определяет принадлежность очереди к текущему соединение. Соединение закрыто, очередь удаляется. 
 * если exclusive = false, то очередь доступна из других channel. false - нет.
 * 5. auto-delted - очередь будет удалена, если канал закрыт. false - нет.
 */

$channel->queue_declare('hello', false, false, false, false);
/**
 * Определяем сообщение
 * 1. текст сообщения
 * 2. свойства сообщения
 * @var AMQPMessage
 */
$msg = new AMQPMessage('Hello World!');

/**
 * Отправляем сообщение в очередь 
 * 1. сообщение
 * 2. свойства обмена 
 * 3. имя очереди
 * @var AMQPMessage
 */
$channel->basic_publish($msg, '', 'hello');

echo " [x] Sent 'Hello World!'\n";

// закрываем канал
$channel->close();

// закрываем соединение
$connection->close();