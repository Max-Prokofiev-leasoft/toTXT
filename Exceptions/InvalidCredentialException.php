<?php

namespace GingerPlugins\Exceptions;

class InvalidCredentialException extends \Exception
{
    public function __construct()
    {
        $this->message = 'No credentials found based on public key.';
    }
}