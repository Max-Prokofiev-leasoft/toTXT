<?php

namespace GingerPlugins\Exceptions;

class InvalidHashException extends \Exception
{
    public function __construct()
    {
        $this->message = 'Calculated hash does not match the hash included in the headers.';
    }
}