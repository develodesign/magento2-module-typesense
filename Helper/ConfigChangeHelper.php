<?php

namespace Develo\Typesense\Helper;

use Algolia\AlgoliaSearch\Helper\Data as AlgoliaHelper;
use Algolia\AlgoliaSearch\Helper\ConfigHelper as AlgoliaConfigHelper;
use Develo\Typesense\Adapter\Client;
use Develo\Typesense\Services\ConfigService;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigChangeHelper
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
     * $var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigService
     */
    private ConfigService $configService;

    /**
     * @var AlgoliaConfigHelper
     */
    private AlgoliaConfigHelper $algoliaConfigHelper;

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;


    /**
     * @var SearchCriteriaBuilderFactory
     */
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    public function __construct(
        RequestInterface $request,
        Client $client,
        AlgoliaHelper $algoliaHelper,
        StoreManagerInterface $storeManager,
        ConfigService $configService,
        AlgoliaConfigHelper $algoliaConfigHelper,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    )
    {
        $this->request = $request;
        $this->typesenseClient = $client->getTypesenseClient();
        $this->algoliaHelper = $algoliaHelper;
        $this->typeSenseCollecitons = $this->typesenseClient->collections;
        $this->storeManager = $storeManager;
        $this->configService = $configService;
        $this->algoliaConfigHelper = $algoliaConfigHelper;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Creates Indexes in Typesense after credentials have been updated
     */
    public function setCollectionConfig()
    {

        $facets = [];

        foreach ($this->algoliaConfigHelper->getFacets() as $facet) {
            $facets[] = $facet['attribute'];
        }

        $indexes = $this->getMagentoIndexes();

        $existingCollections = $this->getExistingCollections();

        foreach ($indexes as $index) {
            $requiredIndexes = ['products', 'categories', 'pages'];
            foreach ($requiredIndexes as $indexToCreate) {

                $fields = $this->getFields($facets, $indexToCreate);

                $indexName = $index["indexName"] . "_{$indexToCreate}";

                if (!isset($existingCollections[$indexName])) {

                    $this->typeSenseCollecitons->create(
                        [
                            'name' => $indexName,
                            'enable_nested_fields' => true,
                            'fields' => $fields
                        ]
                    );

                    continue;
                }
            }
        }

        return $this;
    }

    /**
     * Gets existing collections from typesense
     */
    private function getExistingCollections()
    {
        $collections = $this->typeSenseCollecitons->retrieve();
        $existingCollections = [];
        foreach ($collections as $collection) {
            $existingCollections[$collection["name"]] = $collection;
        }
        return $existingCollections;
    }

    /**
     * Gets an Aloliga index name for each store
     */
    private function getMagentoIndexes()
    {
        $indexNames = [];
        foreach ($this->storeManager->getStores() as $store) {
            $indexNames[$store->getId()] = [
                'indexName' => $this->algoliaHelper->getBaseIndexName($store->getId()),
                'priceKey' => '.' . $store->getCurrentCurrencyCode($store->getId()) . '.default',
            ];
        }
        return $indexNames;
    }

    private function getFields(array $facets, string $index) : array {
        switch ($index) {
            case 'products':
                $attributes = $this->algoliaConfigHelper->getProductAdditionalAttributes();
                $entityTypeCode = ProductAttributeInterface::ENTITY_TYPE_CODE;
                $defaultAttributes = [
                    ['name' => 'objectID', 'type' => 'string', 'facet' => true],
                    ['name' => 'categories', 'type' => 'object', 'facet' => true],
                    ['name' => 'visibility_search', 'type' => 'int64'],
                    ['name' => 'visibility_catalog', 'type' => 'int64', 'facet' => true]
                ];

                break;
            case 'categories':
                $attributes = $this->algoliaConfigHelper->getCategoryAdditionalAttributes();
                $entityTypeCode = CategoryAttributeInterface::ENTITY_TYPE_CODE;
                $defaultAttributes = [
                    ['name' => 'objectID', 'type' => 'string', 'facet' => true],
                ];
                break;
            case 'pages':
            default:
                return [
                    ['name' => 'objectID', 'type' => 'string'],
                    ['name' => 'content', 'type' => 'string'],
                    ['name' => 'slug', 'type' => 'string'],
                ];
        }

        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            if ($attribute['searchable'] === '1' || in_array($attribute['attribute'], $facets)) {
                $attributeCodes[] = $attribute['attribute'];
            }
        }

        /** @var SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria->addFilter('attribute_code', $attributeCodes, 'IN');

        $attributeCollection = $this->attributeRepository->getList($entityTypeCode, $searchCriteria->create());

        $backendTypes = [
            'datetime' => 'string',
            'decimal' => 'float',
            'int' => 'int64',
            'static' => 'string',
            'text' => 'string',
            'varchar' => 'string'
        ];

        $fields = [];
        foreach ($attributeCollection->getItems() as $attribute) {
            if (!isset($backendTypes[$attribute->getBackendType()]) || !$attribute->getIsRequired()) {
                continue;
            }

            if ($attribute->getAttributeCode() === 'price') {
                $fields[] = [
                    'name' => $attribute->getAttributeCode(),
                    'type' => 'object'
                ];

                continue;
            }

            $fields[] = [
                'name' => $attribute->getAttributeCode(),
                'type' => $backendTypes[$attribute->getBackendType()],
                'facet' => in_array($attribute->getAttributeCode(), $facets)
            ];
        }

        $fields = array_merge($fields, $defaultAttributes);

        $fields = array_unique($fields, SORT_REGULAR);

        return array_values($fields);
    }
}
