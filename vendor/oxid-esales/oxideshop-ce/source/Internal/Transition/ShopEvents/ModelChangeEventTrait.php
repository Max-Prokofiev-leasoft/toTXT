<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Transition\ShopEvents;

/**
 * Model object
 *
 * @var \OxidEsales\Eshop\Core\Model\BaseModel
 */
trait ModelChangeEventTrait
{
    private $model;

    public function __construct(\OxidEsales\Eshop\Core\Model\BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * Getter for model class name.
     *
     * @return \OxidEsales\Eshop\Core\Model\BaseModel
     */
    public function getModel(): \OxidEsales\Eshop\Core\Model\BaseModel
    {
        return $this->model;
    }
}
