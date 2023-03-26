<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Develo\Typesense\Plugin\Algolia\AlgoliaSearch\Model;

use Develo\Typesense\Services\ConfigService;

class IndicesConfigurator
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

    public function aroundSaveConfigurationToAlgolia(
        \Algolia\AlgoliaSearch\Model\IndicesConfigurator $subject,
        \Closure $proceed
    ) {
        if($this->configService->isIndexModeTypeSenseOnly()){
            return true;
        }
        $result = $proceed();
        return $result;
    }
}
