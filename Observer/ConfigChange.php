<?php

namespace Develo\Typesense\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use \Develo\Typesense\Adapter\Client;
use Algolia\AlgoliaSearch\Helper\Data as AlgoliaHelper;
use Magento\Store\Model\StoreManagerInterface;
use Develo\Typesense\Services\ConfigService;


class ConfigChange implements ObserverInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * $var Client
     */
    private $typesenseClient;

    /**
     * $var AlgoliaHelper
     */
    private $algoliaHelper;

     /**
     * $var Devloops\Typesence\Collections
     */
    private $typeSenseCollecitons;

    /**
     * $var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigService
     */
    private ConfigService $configService;


    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request,
        Client $client,
        AlgoliaHelper $algoliaHelper,
        StoreManagerInterface $storeManager,
        ConfigService $configService
    ) {
        $this->request = $request;
        $this->typesenseClient = $client->getTypesenseClient();
        $this->algoliaHelper = $algoliaHelper;
        $this->typeSenseCollecitons = $this->typesenseClient->collections;
        $this->storeManager = $storeManager;
        $this->configService = $configService;
    }

    /**
     * Creates Indexes in Typesense after credentials have been updated
     */
    public function execute(EventObserver $observer)
    {
        $indexes = $this->getMagentoIndexes();
        $existingCollections = $this->getExistingCollections();
        
        foreach($indexes as $index){
            $requiredIndexes = ['products','categories', 'pages'];
            foreach($requiredIndexes as $indexToCreate){
                if(!isset($existingCollections[$index["indexName"]."_{$indexToCreate}"])){
                    $this->typeSenseCollecitons->create(
                        [
                            'name' => $index["indexName"]."_{$indexToCreate}",
                            'fields' => [['name' => 'name','type' => 'string'],
                            ['name' => 'objectID','type' => 'string'],['name' => 'id','type' => 'string']]
                        ]
                    );
                }
            }
        }       
        
        return $this;
    }

    /**
     * Gets existing collections from typesense
     */
    private function getExistingCollections(){
        $collections = $this->typeSenseCollecitons->retrieve();
        $existingCollections =[];
        foreach($collections as $collection) {
            $existingCollections[$collection["name"]] = $collection;
        }
        return $existingCollections;
    }

    /**
     * Gets an Aloliga index name for each store
     */
    private function getMagentoIndexes(){
        $indexNames = [];
        foreach ($this->storeManager->getStores() as $store) {
            $indexNames[$store->getId()] = [
                'indexName' => $this->algoliaHelper->getBaseIndexName($store->getId()),
                'priceKey' => '.' . $store->getCurrentCurrencyCode($store->getId()) . '.default',
            ];
        }
        return $indexNames;
    }

}