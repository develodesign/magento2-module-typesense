<?php

namespace Develo\Typesense\Model\Config\Source;

/**
 * Typesense protocols
 */
class TypeSenseProtocol implements \Magento\Framework\Data\OptionSourceInterface
{
    const HTTP = 'http';
    const HTTPS = 'https';

    /**
     * @var array
     */
    private $protocols = [
        self::HTTP => 'HTTP',
        self::HTTPS => 'HTTPS',
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->protocols as $key => $value) {
            $options[] = [
                'value' => $key,
                'label' => __($value),
            ];
        }
        return $options;
    }
}
