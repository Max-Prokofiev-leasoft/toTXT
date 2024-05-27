<?php

namespace GingerPlugins\Exceptions;

class InvalidGingerOrderStatus extends \Exception
{
    public function __construct($message = '')
    {
        $this->message = 'There was a problem processing your transaction.' . $message;
    }
}