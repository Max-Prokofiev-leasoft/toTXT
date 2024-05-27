<?php

namespace GingerPlugins\Exceptions;

/**
 * Class InvalidGingerOrderId
 *
 * @package GingerPlugins\Exceptions
 * @author  Ginger
 * @date    2020-07-10
 * @version 1.0 - First draft
 * @version 1.1 - Refactored to conduct GPE solution
 */
class InvalidGingerOrderId extends \Exception
{
    public function __construct()
    {
        $this->message = 'Error: Response did not include id!';
    }
}