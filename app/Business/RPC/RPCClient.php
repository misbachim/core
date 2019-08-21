<?php

namespace App\Business\RPC;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Webpatser\Uuid\Uuid;

/**
 * Client for calling methods in other services.
 */
class RPCClient
{
    private $connection; // RabbitMQ connection instance
    private $channel; // RabbitMQ channel instance
    private $callResultQueue; // queue for receiving call result
    private $corrId; // message correlation ID, so we know which reply we should handle
    private $callResult; // this is what we want

    public function __construct()
    {
        $this->createConnectionAndChannel();
        $this->configureChannel();
        $this->consumeCallResultQueue();
    }

    public function __destruct()
    {
        // Close both channel and connection.
        if ($this->channel && $this->connection) {
            $this->channel->close();
            $this->connection->close();
        }
    }

    /**
     * Callback for getting the call result (only if the message
     * has the correct correlation ID).
     *
     * @param $rep RabbitMQ message reply
     */
    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corrId) {
            $this->callResult = $this->toPayload($rep->body);
        }
    }

    /**
     * Call a method in another service through RabbitMQ.
     *
     * @param string $service The target service name
     * @param string $method  The target method name
     * @param        $args  The target method arguments
     */
    public function call(string $service, string $method, ...$args)
    {
        if (! $this->channel || ! $this->callResultQueue) {
            return;
        }

        // Reset for a new call.
        $this->callResult = null;
        $this->corrId = Uuid::generate();

        // Create the call message.
        $call = $this->makeCall($method, $args);
        $msg = new AMQPMessage(
            $this->toPacket($call),
            [
                'correlation_id' => $this->corrId,
                'reply_to' => $this->callResultQueue
            ]
        );

        // Publish the call message to the target service' RPC queue.
        $rpcQueue = $this->getRPCQueue($service);
        $this->channel->basic_publish($msg, '', $rpcQueue);

        // Wait and return call result (if any).
        $this->waitForCallResult();
        return $this->callResult;
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
        // Declare call result queue.
        if ($this->channel) {
            list($this->callResultQueue, ,) = $this->channel->queue_declare(
                $this->getCallResultQueue(),
                false,
                false,
                true,
                false
            );
        }
    }

    private function consumeCallResultQueue()
    {
        // Consume call result queue and handle any message with onResponse callback.
        if ($this->channel && $this->callResultQueue) {
            $this->channel->basic_consume(
                $this->callResultQueue,
                '',
                false,
                false,
                false,
                false,
                [$this, 'onResponse']
            );
        }
    }

    /**
     * Wait for call result. Raise exceptions as necessary.
     */
    private function waitForCallResult()
    {
        if (! $this->channel) {
            return;
        }

        $timeout = config('app.rpc_timeout');

        while (! $this->callResult) {
            try {

                // Block until it reads call result or it reaches timeout.
                $this->channel->wait(null, false, $timeout);

            } catch (AMQPTimeoutException $e) {
                throw new \Exception("Waited too long for result, try again later");
            }
        }

        // Oops, something bad happened on the other side. Better shout, just in case.
        if ($this->callResult instanceof \Exception) {
            throw new \Exception($this->callResult->getMessage());
        }
    }

    private function makeCall($method, $args)
    {
        // NOTE: This looks basic, maybe we should support 'class' as well?
        return [
            'method' => $method,
            'args' => $args
        ];
    }

    private function toPayload($packet)
    {
        return unserialize($packet);
    }

    private function toPacket($payload)
    {
        return serialize($payload);
    }

    private function getCallResultQueue()
    {
        $service = env('SERVICE_NAME');
        return "${service}_rpc_result_queue";
    }

    private function getRPCQueue($service)
    {
        return "${service}_rpc_queue";
    }
}
