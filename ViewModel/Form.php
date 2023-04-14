<?php

declare(strict_types=1);

namespace Develo\Typesense\ViewModel;

use Algolia\AlgoliaSearch\Block\Configuration;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;

use function is_string;

/**
 * Class HyvaForm
 *
 * This comes from the Hyva Algolia
 *
 * @package Develo\Typesense\ViewModel
 */
class Form implements ArgumentInterface
{
    /**
     * @var AssetRepository
     */
    private $assetRepository;
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var Json
     */
    private $json;

    public function __construct(
        AssetRepository $assetRepository,
        Configuration $configuration,
        Json $json
    ) {
        $this->assetRepository = $assetRepository;
        $this->configuration = $configuration;
        $this->json = $json;
    }

    public function getAssetUrl(string $asset): string
    {
        return $this->assetRepository->getUrl($asset);
    }

    public function getJsConfigData(): array
    {
        return $this->getAlgoliaConfiguration();
    }

    public function getJsConfig(): string
    {
        $result = $this->json->serialize($this->getJsConfigData());

        return is_string($result) ? $result : '';
    }

    public function getAutocompleteScripts() {
        return $this->json->serialize(
            [
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/algoliaBundle.min.js'),
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/autocomplete-js.js'),
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/algoliasearch.js'),
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/algoliasearch-query-suggestion-plugin.js')
            ]
        );
    }

    public function getInstantsearchScripts() {
        return $this->json->serialize(
            [
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/algoliaBundle.min.js'),
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/common.js'),
                $this->getAssetUrl('Develo_Typesense::js/typesense-adapter.js'),
                $this->getAssetUrl('Develo_Typesense::js/hyva-adapter.js'),
                $this->getAssetUrl('Hyva_AlgoliaSearch::js/internals/instantsearch.js')
            ]
        );
    }

    private function getAlgoliaConfiguration(): array
    {
        return (array)$this->configuration->getConfiguration();
    }
}
