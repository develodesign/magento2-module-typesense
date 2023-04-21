<?php

namespace Develo\Typesense\ViewModel\Adminhtml;

use Develo\Typesense\Services\ConfigService;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Configuration implements ArgumentInterface
{
    /**
     * @var ConfigService
     */
    private ConfigService $configService;

    /**
     * Configuration constructor.
     *
     * @param ConfigService $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @return bool
     */
    public function isTypeSenseEnabled()
    {
        return $this->configService->isTypeSenseEnabled();
    }
}
