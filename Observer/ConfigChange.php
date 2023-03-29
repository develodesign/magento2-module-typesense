<?php

namespace Develo\Typesense\Observer;

use Develo\Typesense\Helper\ConfigChangeHelper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ConfigChange implements ObserverInterface
{
    private ConfigChangeHelper $configChangeHelper;
    /**
     * ConfigChange constructor.
     *
     * @param ConfigChangeHelper $configChangeHelper
     */
    public function __construct(ConfigChangeHelper $configChangeHelper)
    {
        $this->configChangeHelper = $configChangeHelper;
    }


    public function execute(EventObserver $observer)
    {
        $this->configChangeHelper->setCollectionConfig();
    }
}
