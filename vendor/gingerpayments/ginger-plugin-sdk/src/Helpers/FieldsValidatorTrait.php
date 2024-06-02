<?php

namespace GingerPluginSdk\Helpers;

use GingerPluginSdk\Exceptions\OutOfEnumException;
use GingerPluginSdk\Exceptions\OutOfPatternException;

trait FieldsValidatorTrait
{
    /**
     * @throws OutOfEnumException
     */
    public function validateEnum($value): void
    {
        if (isset($this->enum) && !in_array($value, $this->enum)) {
            throw new OutOfEnumException($this->getPropertyName(), $value, json_encode($this->enum));
        }
    }

    /**
     * @throws OutOfPatternException
     */
    public function validatePattern($value, $pattern): void
    {
        if (!preg_match($pattern, $value)) {
            throw new OutOfPatternException($this->getPropertyName());
        }
    }
}