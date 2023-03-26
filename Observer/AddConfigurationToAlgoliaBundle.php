<?php


namespace Develo\Typesense\Observer;

use Develo\Typesense\Services\ConfigService;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddConfigurationToAlgoliaBundle implements ObserverInterface
{
    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * AddConfigurationToAlgoliaBundle constructor.
     *
     * @param ConfigService $configService
     */
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

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
    }
}
