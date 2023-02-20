<?php

namespace Develo\Typesense\Adapter;

use Typesense\Client as TypeSenseClient;
use Develo\Typesense\Services\ConfigService;

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
     * Initialise Typesense Client with Magento config
     *
     * @param ConfigService $configService
     * @throws \Typesense\Exceptions\ConfigError
     */
    public function __construct(
        ConfigService $configService,
    )
    {
        $this->configService = $configService;
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
        return $this->typeSenseClient->collections[$indexName]->documents->create_many($data, ['action' => 'upsert']);
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


