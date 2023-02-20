<?php

namespace Develo\Typesense\Model\Config\Source;

/**
 * Typesense module indexation methods
 */
class TypeSenseIndexMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    const METHOD_TYPESENSE = 'typesense';
    const METHOD_ALGOLIA = 'algolia';
    const METHOD_BOTH = 'typesense_algolia';
    /**
     * @var array
     */
    private $methods = [
        self::METHOD_TYPESENSE => 'Typesense Only',
        self::METHOD_BOTH => 'Both Typesense and Aglolia',
        self::METHOD_ALGOLIA => 'Algolia Only'
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->methods as $key => $value) {
            $options[] = [
                'value' => $key,
                'label' => __($value),
            ];
        }
        return $options;
    }
}
