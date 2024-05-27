<?php

namespace GingerPlugins\Exceptions;

class InvalidApiResponse extends \Exception
{
    public function __construct($sMessage = '')
    {
        $this->message = 'API returned an unexpected result. ' . $sMessage;
    }
}