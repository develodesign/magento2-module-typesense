<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Develo\Typesense\Plugin\Algolia\AlgoliaSearch\Helper;

use Develo\Typesense\Services\ConfigService;

class ConfigHelper
{
    /**
     * @var ConfigService
     */
    protected ConfigService $configService;

    /**
     * @param ConfigService $configService
     * @param Client $client
     */
    public function __construct(
        ConfigService $configService
    )
    {
        $this->configService = $configService;
    }

    public function afterIsEnabledBackend(
        \Algolia\AlgoliaSearch\Helper\ConfigHelper $subject,
        $result
    ) {
        if($this->configService->isIndexModeTypeSenseOnly()){
            return true;
        }
        //Your plugin code
        return $result;
    }

    public function afterGetApplicationID(
        \Algolia\AlgoliaSearch\Helper\ConfigHelper $subject,
        $result
    ) {
        if($this->configService->isIndexModeTypeSenseOnly()){
            return true;
        }
        
        return $result;
    }

    public function afterGetAPIKey(
        \Algolia\AlgoliaSearch\Helper\ConfigHelper $subject,
        $result
    ) {
        if($this->configService->isIndexModeTypeSenseOnly()){
            return true;
        }

        return $result;
    }

    public function afterGetSearchOnlyAPIKey(
        \Algolia\AlgoliaSearch\Helper\ConfigHelper $subject,
        $result
    ) {
        if($this->configService->isIndexModeTypeSenseOnly()){
            return true;
        }
        return $result;
    }

}