<?php

namespace Zero1\OpenPos\Model\Config\Source;

use \Magento\User\Model\ResourceModel\User\CollectionFactory as AdminUserCollectionFactory;

class AdminUser implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @param AdminUserCollectionFactory $adminUserCollectionFactory
     */
    public function __construct(
        protected AdminUserCollectionFactory $adminUserCollectionFactory
    ) {}

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $values = array();
        $attributeCollection = $this->adminUserCollectionFactory->create();

        foreach ($attributeCollection->getItems() as $item) {
            $values[] = [
                'value' => $item->getUserId(),
                'label' => $item->getUserName()
            ];
        }

        return $values;
    }
}
