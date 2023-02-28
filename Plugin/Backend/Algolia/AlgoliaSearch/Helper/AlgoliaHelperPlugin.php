<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Develo\Typesense\Plugin\Backend\Algolia\AlgoliaSearch\Helper;

use Algolia\AlgoliaSearch\Helper\AlgoliaHelper;
use Develo\Typesense\Adapter\Client;
use Develo\Typesense\Services\ConfigService;
use Develo\Typesense\Model\Config\Source\TypeSenseIndexMethod;

class AlgoliaHelperPlugin
{
    /**
     * @var ConfigService
     */
    protected  $configService;

    /**
     * @var Client $typesenseClient
     */
    protected $typesenseClient;

    /**
     * @param ConfigService $configService
     * @param Client $client
     */
    public function __construct(
        ConfigService $configService,
        Client        $client
    )
    {
        $this->configService = $configService;
        $this->typesenseClient = $client;
    }

    /**
     * Indexes data if config is set todo, will index into algolia or typesense or both
     */
    public function aroundAddObjects(
        \Algolia\AlgoliaSearch\Helper\AlgoliaHelper $subject,
        \Closure                                    $proceed,
                                                    $objects,
                                                    $indexName
    )
    {
        if ($this->configService->isEnabled()) {
            $result = [];
            $indexMethod = $this->configService->getIndexMethod();
            switch ($indexMethod) {
                case TypeSenseIndexMethod::METHOD_ALGOLIA:
                    $result = $proceed();
                    break;
                case TypeSenseIndexMethod::METHOD_BOTH:
                    $this->typesenseClient->addData($indexName, $objects);
                    $result = $proceed();
                    break;
                case TypeSenseIndexMethod::METHOD_TYPESENSE:
                default:
                    $this->typesenseClient->addData($indexName, $objects);
                    break;
            }
        } else {
            $result = $proceed();
        }
        return $result;
    }
}
