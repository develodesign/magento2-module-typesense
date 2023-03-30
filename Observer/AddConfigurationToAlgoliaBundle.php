<?php


namespace Develo\Typesense\Observer;

use Develo\Typesense\Helper\ConfigChangeHelper;
use Develo\Typesense\Services\ConfigService;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddConfigurationToAlgoliaBundle implements ObserverInterface
{
    /**
     * @var ConfigService
     */
    private ConfigService $configService;

    /**
     * @var ConfigChangeHelper
     */
    private ConfigChangeHelper $configChangeHelper;

    /**
     * AddConfigurationToAlgoliaBundle constructor.
     *
     * @param ConfigService $configService
     * @param ConfigChangeHelper $configChangeHelper
     */
    public function __construct(ConfigService $configService, ConfigChangeHelper $configChangeHelper)
    {
        $this->configService = $configService;
        $this->configChangeHelper = $configChangeHelper;
    }

    /**
     * @param Observer $observer
     *
     * @event develo_typesense_add_additional_config
     */
    public function execute(Observer $observer)
    {
        $configuration = $observer->getData('configuration');

        if (!$configuration instanceof DataObject) {
            return;
        }

        $typesenseConfig =  [
            'isEnabled' => $this->configService->isEnabled(),
            'config' => [
                'apiKey' => $this->configService->getSearchOnlyKey(),
                'nodes' => [
                    [
                        'host' => $this->configService->getNodes(),
                        'path' => $this->configService->getPath() ?? '',
                        'port' => $this->configService->getPort(),
                        'protocol' => $this->configService->getProtocol()
                    ]
                ],
                'cacheSearchResultsForSeconds' => '2 * 60'
            ]
        ];

        $configuration->setData('typesense', $typesenseConfig);
        $configuration->setData('typesense_searchable', [
            'products' => $this->configChangeHelper->getSearchableAttributes(),
            'categories' => $this->configChangeHelper->getSearchableAttributes(ConfigChangeHelper::INDEX_CATEGORIES),
            'pages' => $this->configChangeHelper->getSearchableAttributes(ConfigChangeHelper::INDEX_PAGES),
        ]);
    }
}
