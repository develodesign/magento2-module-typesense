<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Develo\Typesense\Plugin\Algolia\AlgoliaSearch\Helper;

use Algolia\AlgoliaSearch\Helper\AlgoliaHelper;
use Develo\Typesense\Adapter\Client;
use Develo\Typesense\Services\ConfigService;
use Develo\Typesense\Model\Config\Source\TypeSenseIndexMethod;

class Data
{
    /**
     * @var ConfigService
     */
    protected ConfigService $configService;

    /**
     * @var Client $typesenseClient
     */
    protected Client $typesenseClient;

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

    public function aroundDeleteInactiveProducts(        
        \Algolia\AlgoliaSearch\Helper\Data $subject,
        \Closure $proceed,
        $storeId
    ){ 
    if ($this->configService->isEnabled()) {
            $result = [];
            $indexMethod = $this->configService->getIndexMethod();
            switch ($indexMethod) {
                case TypeSenseIndexMethod::METHOD_ALGOLIA:
                    $result = $proceed();
                    break;
                case TypeSenseIndexMethod::METHOD_BOTH:
                    $result = $proceed();
                    break;
                case TypeSenseIndexMethod::METHOD_TYPESENSE:
                default:
                    return true;
            }
        } else {
            $result = $proceed();
        }
        return $result; 
    }

}
