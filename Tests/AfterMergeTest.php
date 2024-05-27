<?php

use PHPUnit\Framework\TestCase;

class AfterMergeTest extends TestCase
{
    function testAutoloader()
    {
        require_once __DIR__."/../autoloader.php";
        $this->assertTrue(isRequiredAutoloader());
    }
}
