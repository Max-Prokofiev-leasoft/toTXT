<?php

namespace GingerPlugins\Exceptions;

class InvalidJsonException extends \Exception
{
    public function __construct()
    {
        $this->message = 'Data supplied is not conform the JSON standards.';
    }
}