<?php


namespace Develo\Typesense\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddDefaultPriceObserver implements ObserverInterface
{
    /**
     * We need to add a default price to the list of attributes so we can sort by the correct type
     * on the frontend.
     *
     * @param Observer $observer
     *
     * @event algolia_after_create_product_object
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getData('custom_data');

        if (!$transport instanceof DataObject) {
            return;
        }

        $price = $transport->getData('price');

        if (!count($price)) {
            return;
        }

        $defaultPrice = array_values($price)[0];

        if (!isset($defaultPrice['default'])) {
            return;
        }

        $transport->setData('price_default', $defaultPrice['default']);
    }
}
