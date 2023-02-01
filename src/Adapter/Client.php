<?php

namespace Develo\TypeSense\Adapter;

use Devloops\Typesence\Client as TypeSenseClient;

/**
 * Class Client
 *
 * This class should replace the AlgoliaSearch\Client implementation
 *
 * @see \AlgoliaSearch\Client
 * @package Develo\TypeSense\Service
 */
class Client extends \AlgoliaSearch\Client
{

    /**
     * @var TypeSenseClient
     */
    public $typeSenseClient;

    public function __construct(
        $applicationID,
        $apiKey,
        TypeSenseClient $client, // @todo we need to inject a config class here
        $hostsArray = null,
        $options = array()
    )
    {
        parent::__construct($applicationID, $apiKey, $hostsArray, $options);

        $this->typeSenseClient = $client;
    }

    /**
     * @inheirtDoc
     */
    public function deleteIndex($indexName)
    {
        return $this->typeSenseClient->collections[$indexName]->delete();
    }
}