<?php

namespace Develo\Typesense\Adapter;

use Devloops\Typesence\Client as TypeSenseClient;
use \Magento\Store\Model\ScopeInterface as ScopeConfig;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Client
 *
 * This class should replace the AlgoliaSearch\Client implementation
 *
 * @see \AlgoliaSearch\Client
 * @package Develo\TypeSense\Service
 */
class Client
{

    /**
     * Config paths
     */
    private const TYPESENSE_API_KEY = 'typesense_general/settings/admin_api_key';
    private const TYPESENSE_NODES = 'typesense_general/settings/nodes';

    /**
     * @var TypeSenseClient
     */
    private $typeSenseClient;

    /**
     * encryptor
     */
    private $encryptor;

    /**
     * Initialise Typesense Client with Magento config
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig
    )
    {        
        $apiKey = $scopeConfig->getValue(SELF::TYPESENSE_API_KEY,ScopeConfig::SCOPE_STORE);
        $apiKey = $encryptor->decrypt($apiKey);

        $nodes = $scopeConfig->getValue(SELF::TYPESENSE_NODES,ScopeConfig::SCOPE_STORE);

        $client = new TypeSenseClient(
            [   
                "api_key" => $apiKey,
                "nodes"=> 
                [
                    [
                        "host" => $nodes,
                        "port" => "443", 
                        "protocol" => "https",
                        "api_key" => $apiKey
                    ]
                ]
            ]
        );

        $this->typeSenseClient = $client;
    }

    /**
     * @inheirtDoc
     */
    public function deleteIndex($indexName)
    {
        return $this->typeSenseClient->collections[$indexName]->delete();
    }

    /**
     * @inheirtDoc
     */
    public function addData($indexName, $data)
    {
        return $this->typeSenseClient->collections[$indexName]->documents->create_many($data, ['action' => 'upsert']);
    }

    public function getTypesenseClient(){
        return $this->typeSenseClient;
    }
}


