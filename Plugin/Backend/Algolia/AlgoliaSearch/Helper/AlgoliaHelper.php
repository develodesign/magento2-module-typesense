<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Develo\Typesense\Plugin\Backend\Algolia\AlgoliaSearch\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface as ScopeConfig;
use \Develo\Typesense\Adapter\Client;

class AlgoliaHelper
{
    private const TYPESENSE_INDEX_METHID = 'typesense_general/settings/index_method';

    /**
     * @var $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @var Client $typesenseClient
     */
    protected $typesenseClient;

    /**
     * __construct
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Develo\TypeSense\Adapter\Client $client
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Client $client
    ) {
        $this->typesenseClient = $client;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Indexes data if config is set todo, will index into algolia or typesense or both
     */
    public function aroundAddObjects(
        \Algolia\AlgoliaSearch\Helper\AlgoliaHelper $subject,
        \Closure $proceed,
        $objects,
        $indexName
    ) {
        $result = [];
        $indexMethod = $this->scopeConfig->getValue(SELF::TYPESENSE_INDEX_METHID,ScopeConfig::SCOPE_STORE);
        switch ($indexMethod) {
            case "algolia":
                $result = $proceed();
                break;
            case "typesense_algolia":
                $this->typesenseClient->addData($indexName, $objects);
                $result = $proceed();
                break;
            case "typesense":
            default:
                $this->typesenseClient->addData($indexName,$objects);
                break;
        }

        return $result;
    }
}