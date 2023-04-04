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
     * @event algolia_after_create_configuration
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

        $items = [];
        $indexName = $configuration->getData('indexName').'_products';
        foreach ($configuration->getData('sortingIndices') as &$sorting) {

            if ($sorting['attribute'] === 'price') {
                $sorting['attribute'] = 'price_default';
            }

            $items[] = [
                'label' => $sorting['label'],
                'name' => sprintf('%s/sort/%s:%s', $indexName, $sorting['attribute'], $sorting['sort'])
            ];
        }

        $configuration->setData('sortingIndices', $items);

        $configuration->setData('typesense', $typesenseConfig);
        $configuration->setData('typesense_searchable', [
            'products' => $this->configChangeHelper->getSearchableAttributes(),
            'categories' => $this->configChangeHelper->getSearchableAttributes(ConfigChangeHelper::INDEX_CATEGORIES),
            'pages' => $this->configChangeHelper->getSearchableAttributes(ConfigChangeHelper::INDEX_PAGES),
        ]);
    }
}
