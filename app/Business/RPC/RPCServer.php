<?php

namespace App\Business\RPC;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Business\Model\SerializableException;

/**
 * Server for receiving calls from other services.
 */
class RPCServer
{
    private $connection; // RabbitMQ connection instance
    private $channel; // RabbitMQ channel instance

    /**
     * Start waiting for calls from other services.
     */
    public function start()
    {
        $this->createConnectionAndChannel();
        $this->configureChannel();
        $this->consumeRPCQueue();

        // Receive calls indefinitely.
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        // Close channel and connection if something interrupted the wait.
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Callback for handling received calls.
     *
     * @param $req The call request
     */
    public function callHandler($req)
    {
        info('req', array($req));
        // Extract call info and execute it.
        $call = $this->toPayload($req->body);
        $callResult = $this->delegateCall($call);

        // Prepare reply message containing the call result.
        $msg = new AMQPMessage(
            $this->toPacket($callResult),
            array('correlation_id' => $req->get('correlation_id'))
        );

        // Publish reply message to the target call result queue.
        $req->delivery_info['channel']->basic_publish(
            $msg,
            '',
            $req->get('reply_to')
        );

        // Tell RabbitMQ that the call request had been successfully delivered.
        $req->delivery_info['channel']->basic_ack(
            $req->delivery_info['delivery_tag']
        );
    }

    /**
     * Execute the call and return an exception if necessary.
     */
    private function delegateCall($call)
    {
        // Extract call info.
        $method = $call['method'];
        $args = $call['args'];

        try {
            $api = app()->make(InternalAPI::class);
            $callResult = $api->$method(...$args); // everything is fine
        } catch (\Exception $e) {
            // Oops, something bad happened. Let's tell the calling service.
            $callResult = new SerializableException($e->getMessage());
        }

        return $callResult;
    }

    private function createConnectionAndChannel()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_LOGIN'),
            env('RABBITMQ_PASSWORD')
        );
        $this->channel = $this->connection->channel();
    }

    private function configureChannel()
    {
        if (! $this->channel) {
            return;
        }

        // Declare the target service' RPC queue.
        $this->channel->queue_declare($this->getRPCQueue(), false, false, false, false);
        $this->channel->basic_qos(null, 1, null); // set prefetch = 1, so calls can be load balanced
    }

    private function consumeRPCQueue()
    {
        if (! $this->channel) {
            return;
        }

        // Set callback for handling received calls.
        $this->channel->basic_consume($this->getRPCQueue(), '', false, false, false,
            false, [$this, 'callHandler']);
    }

    private function getRPCQueue()
    {
        $service = env('SERVICE_NAME');
        return "${service}_rpc_queue";
    }

    private function toPayload($packet)
    {
        return unserialize($packet);
    }

    private function toPacket($payload)
    {
        return serialize($payload);
    }
}
