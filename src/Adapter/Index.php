<?php


namespace Develo\TypeSense\Adapter;

use AlgoliaSearch\ClientContext;
use Devloops\Typesence\Client as TypeSenseClient;

/**
 * Class Index
 *
 * @package Develo\TypeSense\Service
 *
 * @todo extend all methods used in Magento module and override.
 */
class Index extends \AlgoliaSearch\Index
{
    /**
     * @var string
     */
    public $indexName;

    /**
     * @var Client
     */
    private $client;

    public function __construct(ClientContext $context, Client $client, $indexName)
    {
        parent::__construct($context, $client, $indexName);

        $this->client = $client;
        $this->indexName = $indexName;
    }
    
    public function addObject($content, $objectID = null)
    {
        if ($objectID) {
            $content['id'] = $content;
        }
        
        return $this->getDocuments()->upsert($content);
    }

    public function addObjects($objects, $objectIDKeyLegacy = 'objectID')
    {
        return $this->getDocuments()->import($objects, ['action' => 'create']);
    }

    public function deleteObjects($objects)
    {
        $ids = 'id:='.json_encode($objects);

        return $this->getDocuments()->delete(['filter_by' => $ids]);
    }

    /**
     * @return TypeSenseClient
     */
    private function getClient(): TypeSenseClient
    {
        return $this->client->typeSenseClient;
    }

    /**
     * @todo none of the methods seem to be in this class but that is what is documented.
     *
     * @return \Devloops\Typesence\Documents
     */
    private function getDocuments()
    {
        return $this->getClient()->collections[$this->indexName]->documents;
    }
}