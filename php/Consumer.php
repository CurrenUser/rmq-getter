<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once "Logger.php";
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class Consumer {
	
	private $rabbitmq_ip;
	private $rabbitmq_port;
	private $rabbitmq_user;
	private $rabbitmq_pass;
	private $rabbitmq_connection;
	private $rabbitmq_channel;
	private $rabbitmq_queue;
	private $msg_size;
	private $logger;
	
	function __construct($ip, $port, $user, $pass, $queue , $msg_size, $log_file) {
		$this->rabbitmq_ip = $ip;
		$this->rabbitmq_port = $port;
		$this->rabbitmq_user = $user;
		$this->rabbitmq_pass = $pass;
		$this->rabbitmq_queue = $queue;
		$this->msg_size = $msg_size;

		$this->logger = new Logger($log_file);
 		$this->logger->open();
 		$this->logger->lock();
	}

	public function connecting() {
	  try {
		$rabbitmq_connection = new AMQPStreamConnection(
			$this->rabbitmq_ip, 
			$this->rabbitmq_port,
			$this->rabbitmq_user,
			$this->rabbitmq_pass
		);

		$this->rabbitmq_channel = $rabbitmq_connection->channel();
		$this->rabbitmq_channel->basic_consume($this->rabbitmq_queue, '', false, false, false, false, array($this, 'handler'));
	   }
	   catch (Exception $error) {
	   	 $this->logger->write($error->getMessage());
	   	 $this->logger->close();
	   }
	}

	public function action() {
		while ( count($this->rabbitmq_channel->callbacks) ) {
	    $this->rabbitmq_channel->wait();
		}
	}

	public function handler($msg) {
	  if ( $msg->body_size / 1024 / 1024 > $this->msg_size ) {
	  echo 'Received big msg size: ', $msg->body_size / 1000000 , 'Mb' ,"\n";
	  $this->logger->write('#####################################################################################');
	  $this->logger->write($msg->body);
	  $this->logger->write('#####################################################################################');
	  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
	  }
	}

	public function close() {
		if ($this->rabbitmq_channel) 
			$this->rabbitmq_channel->close();

		if ($this->rabbitmq_connection)
			$this->rabbitmq_connection->close();

		if ($this->logger)
			$this->logger->close();
	}

}