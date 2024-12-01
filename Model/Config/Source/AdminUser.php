<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use \Magento\User\Model\ResourceModel\User\CollectionFactory as AdminUserCollectionFactory;

class AdminUser implements OptionSourceInterface
{
    /**
     * @var AdminUserCollectionFactory
     */
    protected $adminUserCollectionFactory;

    /**
     * @param AdminUserCollectionFactory $adminUserCollectionFactory
     */
    public function __construct(
        AdminUserCollectionFactory $adminUserCollectionFactory
    ) {
        $this->adminUserCollectionFactory = $adminUserCollectionFactory;
    }

    /**
     * Return an array of Magento admin users.
     * 
     * @return array
     */
    public function toOptionArray(): array
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
