<?php

namespace GingerPlugins\Exceptions;

class InvalidGingerOrderPaymentUrl extends \Exception
{
    public function __construct()
    {
        $this->message = 'Error: Response did not include payment url!';
    }
}