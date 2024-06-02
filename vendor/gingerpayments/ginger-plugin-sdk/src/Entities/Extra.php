<?php

namespace GingerPluginSdk\Entities;

use GingerPluginSdk\Helpers\MultiFieldsEntityTrait;
use GingerPluginSdk\Helpers\SingleFieldTrait;
use GingerPluginSdk\Interfaces\ArbitraryArgumentsEntityInterface;
use GingerPluginSdk\Interfaces\MultiFieldsEntityInterface;

class Extra implements MultiFieldsEntityInterface, ArbitraryArgumentsEntityInterface
{
    use MultiFieldsEntityTrait;
    use SingleFieldTrait;

    protected string $propertyName = 'extra';

    // Declare properties explicitly
    protected $sw_order_id;

    public function __construct(
        ...$attributes
    )
    {
        foreach ($attributes as $attribute) {
            $key = key($attribute);
            $this->$key = $this->createSimpleField(
                propertyName: $key,
                value: $attribute[$key]
            );
        }
    }

    // Add getters and setters for each property as needed
    public function getSwOrderId()
    {
        return $this->sw_order_id;
    }

    public function setSwOrderId($sw_order_id)
    {
        $this->sw_order_id = $sw_order_id;
    }
}
