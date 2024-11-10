<?php

namespace Zero1\OpenPos\Model\Config\Source;

use \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;

class BarcodeAttribute implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ProductAttributeCollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * @param ProductAttributeCollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        ProductAttributeCollectionFactory $productAttributeCollectionFactory
    ) {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * Return an array of attributes that can be used as barcodes.
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $values = array();
        $attributeCollection = $this->productAttributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('frontend_input', ['text', 'textarea']);
        $attributeCollection->addFieldToFilter('backend_type', ['text', 'varchar']);

        foreach ($attributeCollection->getItems() as $item) {
            $values[] = [
                'value' => $item->getAttributeCode(),
                'label' => $item->getStoreLabel()
            ];
        }

        return $values;
    }
}
