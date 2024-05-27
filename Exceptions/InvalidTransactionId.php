<?php

namespace GingerPlugins\Exceptions;

/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - First draft
 *
 */
class InvalidTransactionId extends \Exception
{
    public function __construct()
    {
        $this->message = 'No transaction was found based on transacitonId.';
    }
}