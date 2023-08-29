<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Develo\Typesense\Plugin\Algolia\AlgoliaSearch\Model;

use Develo\Typesense\Services\ConfigService;

class SaveSettings
{
    /**
     * @var ConfigService
     */
    protected ConfigService $configService;

    /**
     * @param ConfigService $configService
     */
    public function __construct(
        ConfigService $configService
    )
    {
        $this->configService = $configService;
    }

    public function aroundExecute(
        \Algolia\AlgoliaSearch\Model\Observer\SaveSettings $subject,
        \Closure $proceed,
        ...$args
    ) {
        if($this->configService->isIndexModeTypeSenseOnly()){
            return;
        }

        $result = $proceed(...$args);

        return $result;
    }
}
