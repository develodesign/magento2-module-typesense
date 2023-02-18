<?php

namespace Develo\Typesense\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use \Develo\Typesense\Adapter\Client;
use Algolia\AlgoliaSearch\Helper\Data as AlgoliaHelper;

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
     * ConfigChange constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request,
        Client $client,
        AlgoliaHelper $algoliaHelper
    ) {
        $this->request = $request;
        $this->typesenseClient = $client->getTypesenseClient();
        $this->algoliaHelper = $algoliaHelper;
        $this->typeSenseCollecitons = $this->typesenseClient->collections;
    }

    /**
     * Creates Indexes in Typesense after credentials have been updated
     */
    public function execute(EventObserver $observer)
    {
        $indexes = $this->algoliaHelper->getIndexDataByStoreIds();
        unset($indexes[0]); //skip admin store

        $existingCollections = $this->getExistingCollections();

        foreach($indexes as $index){
            if(!isset($existingCollections[$index["indexName"]."_products"])){
                $this->typeSenseCollecitons->create(
                    [
                        'name' => $index["indexName"]."_products",
                        'fields' => [['name' => 'name','type' => 'string']]
                    ]
                );
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
}