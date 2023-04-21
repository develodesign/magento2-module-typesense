<?php

namespace Develo\Typesense\Adapter;

use Algolia\AlgoliaSearch\Helper\ConfigHelper as AlgoliaConfigHelper;
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
    private ?array $facets = null;

    /**
     * @var ConfigService
     */
    private ConfigService $configService;

    private ?TypeSenseClient $typeSenseClient = null;

    private AlgoliaHelper $algoliaHelper;

    private AlgoliaConfigHelper $configHelper;

    /**
     * Initialise Typesense Client with Magento config
     *
     * @param ConfigService $configService
     * @param AlgoliaHelper $algoliaHelper
     * @throws \Typesense\Exceptions\ConfigError
     */
    public function __construct(
        ConfigService $configService,
        AlgoliaHelper $algoliaHelper,
        AlgoliaConfigHelper $configHelper
    )
    {
        $this->configService = $configService;
        $this->algoliaHelper = $algoliaHelper;
        $this->configHelper = $configHelper;
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
        $facets = [];
        foreach ($data as &$item) {
            $item['id'] = (string)$item['objectID'];
            $item['objectID'] = (string)$item['objectID'];


            if (!isset($item['price']) || !isset($item['sku'])) {
                continue;
            }

            if (is_string($item['sku'])) {
                $item['sku'] = [$item['sku']];
            }

            foreach ($item['price'] as $currency => &$price) {

                $price['special_from_date'] = (string)($price['special_from_date'] ?? '');
                $price['special_to_date'] = (string)($price['special_to_date'] ?? '');

                $price['default'] = number_format($price['default'], 2);
            }

            foreach ($facets as $facet) {
                if (isset($item[$facet]) && !is_array($item[$facet])) {
                    $item[$facet] = [strval($item[$facet])];
                }
            }
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
            'q' => implode(",", $data),
            'query_by' => 'objectID',
        ];

        return $this->getTypesenseClient()->collections[$indexName]->documents->delete($searchParameters);
    }

    /**
     * @inheirtDoc
     */
    public function getData($indexName, $data)
    {
        $searchParameters = [
            'q' => implode(",", $data),
            'query_by' => 'objectID',
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

    private function getFacets()
    {
        if (!is_array($this->facets)) {
            $this->facets = [];
            foreach ($this->configHelper->getFacets() as $facet) {
                $this->facets[] = $facet['attribute'];
            }
        }

        return $this->facets;
    }

}


