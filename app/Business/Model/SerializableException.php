<?php

namespace App\Business\Model;

/**
 * Exception class used for RPC purposes.
 */
class SerializableException extends \Exception implements \Serializable
{
    public function serialize()
    {
        return serialize(array($this->code, $this->message));
    }

    public function unserialize($serialized)
    {
        list($this->code, $this->message) = unserialize($serialized);
    }
}
