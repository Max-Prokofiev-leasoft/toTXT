<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Framework\Templating\Cache;

use OxidEsales\EshopCommunity\Internal\Framework\Templating\Cache\ShopTemplateCacheServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\Cache\TemplateCacheServiceInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

final class TemplateCacheServiceTest extends TestCase
{
    use ContainerTrait;

    private array $shopsIds;
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->shopsIds = $this->get(ContextInterface::class)->getAllShopIds();

        $this->clearTemplateCache();
        $this->populateTemplateCache();

        parent::setUp();
    }

    public function testInvalidateTemplateCache(): void
    {
        $this->assertNotEquals(0, $this->countCacheFiles());

        $this->get(TemplateCacheServiceInterface::class)->invalidateTemplateCache();

        self::assertEquals(0, $this->countCacheFiles());
    }

    private function clearTemplateCache(): void
    {
        foreach ($this->shopsIds as $shopId) {
            $this->filesystem->remove(
                $this->get(ShopTemplateCacheServiceInterface::class)
                    ->getCacheDirectory($shopId)
            );
        }
    }

    private function countCacheFiles(): int
    {
        $files = 0;
        foreach ($this->shopsIds as $shopId) {
            $files += count(\glob($this->get(ShopTemplateCacheServiceInterface::class)
                ->getCacheDirectory($shopId)));
        }
        return $files;
    }

    private function populateTemplateCache(): void
    {
        $numberOfTestFiles = 3;
        foreach ($this->shopsIds as $shopId) {
            $templateCachePath = $this->get(ShopTemplateCacheServiceInterface::class)
                ->getCacheDirectory($shopId);
            $this->filesystem->mkdir($templateCachePath);
            for ($i = 0; $i < $numberOfTestFiles; $i++) {
                $this->filesystem->touch(
                    Path::join(
                        $templateCachePath,
                        uniqid('template-file-' . $shopId, true)
                    )
                );
            }
        }
    }
}
