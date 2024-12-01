<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;

class BarcodeAttribute implements OptionSourceInterface
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
    public function toOptionArray(): array
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
