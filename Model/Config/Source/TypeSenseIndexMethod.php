<?php

namespace Develo\Typesense\Model\Config\Source;

/**
 * Algolia custom sort order field
 */
class TypeSenseIndexMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    private $methods = [
        'typesense' => 'Typesense Only',
        'typesense_algolia' => 'Both Typesense and Aglolia',
        'algolia' => 'Algolia Only'
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
