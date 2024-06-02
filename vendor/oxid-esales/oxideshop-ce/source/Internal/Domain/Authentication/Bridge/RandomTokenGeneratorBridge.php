<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge;

use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Generator\RandomTokenGeneratorInterface;

class RandomTokenGeneratorBridge implements RandomTokenGeneratorBridgeInterface
{
    public function __construct(
        private RandomTokenGeneratorInterface $randomTokenGenerator
    ) {
    }

    /** @inheritdoc */
    public function getAlphanumericToken(int $length): string
    {
        return $this->randomTokenGenerator->getAlphanumericToken($length);
    }

    /** @inheritdoc */
    public function getHexToken(int $length): string
    {
        return $this->randomTokenGenerator->getHexToken($length);
    }
}
