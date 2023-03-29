<?php

namespace Develo\Typesense\Adapter;

use Typesense\Client as TypeSenseClient;
use Develo\Typesense\Services\ConfigService;
use Algolia\AlgoliaSearch\Helper\Data as AlgoliaHelper;

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
     * @var ConfigService
     */
    private ConfigService $configService;

    /**
     * @var TypeSenseClient|null
     */
    private ?TypeSenseClient $typeSenseClient = null;

    /**
     * $var AlgoliaHelper
     */
    private $algoliaHelper;

    /**
     * Initialise Typesense Client with Magento config
     *
     * @param ConfigService $configService
     * @param AlgoliaHelper $algoliaHelper
     * @throws \Typesense\Exceptions\ConfigError
     */
    public function __construct(
        ConfigService $configService,
        AlgoliaHelper $algoliaHelper
    )
    {
        $this->configService = $configService;
        $this->algoliaHelper = $algoliaHelper;
    }

    /**
     * @param string $indexName
     * @return array
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function deleteIndex(string $indexName): array
    {
        return $this->typeSenseClient->collections[$indexName]->delete();
    }

    /**
     * @inheirtDoc
     */
    public function addData($indexName, $data)
    {
        foreach($data as &$item){
            $item['id'] = (string)$item['objectID'];
            $item['objectID'] = (string)$item['objectID'];
        }
        $indexName = rtrim($indexName, "_tmp");
        return $this->getTypesenseClient()->collections[$indexName]->getDocuments()->import($data, ['action' => 'upsert']);
    }

     /**
     * @inheirtDoc
     */
    public function deleteData($indexName, $data)
    {
        $searchParameters = [
            'q'           => implode(",",$data),
            'query_by'    => 'objectID',
          ];
        return $this->getTypesenseClient()->collections[$indexName]->documents->delete($searchParameters);
    }

     /**
     * @inheirtDoc
     */
    public function getData($indexName, $data)
    {
        $searchParameters = [
            'q'           => implode(",",$data),
            'query_by'    => 'objectID',
          ];
        return ["results" => $this->getTypesenseClient()->collections[$indexName]->documents->search($searchParameters)];
    }


    /**
     * @return TypeSenseClient
     */
    public function getTypesenseClient(): TypeSenseClient
    {
        if (is_null($this->typeSenseClient)) {
            $client = new TypeSenseClient(
                [
                    "api_key" => $this->configService->getApiKey(),
                    "nodes" =>
                        [
                            [
                                "host" => $this->configService->getNodes(),
                                "port" => $this->configService->getPort(),
                                "protocol" => $this->configService->getProtocol(),
                                "api_key" => $this->configService->getApiKey()
                            ]
                        ]
                ]
            );

            $this->typeSenseClient = $client;
        }
        return $this->typeSenseClient;
    }

}


