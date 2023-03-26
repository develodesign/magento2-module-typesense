<?php

declare(strict_types=1);

namespace Develo\Typesense\ViewModel;

use Algolia\AlgoliaSearch\Block\Configuration;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class Form extends \Hyva\AlgoliaSearch\ViewModel\Form
{
    /**
     * @var Json
     */
    private $json;

    /**
     * Form constructor.
     *
     * @param AssetRepository $assetRepository
     * @param Configuration $configuration
     * @param Json $json
     */
    public function __construct(
        AssetRepository $assetRepository,
        Configuration $configuration,
        Json $json
    ) {
        parent::__construct($assetRepository, $configuration, $json);

        $this->json = $json;
    }

    public function getInstantsearchScripts()
    {
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
}
