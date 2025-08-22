<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

class Payment extends \Magento\Framework\Model\AbstractModel implements
    \Zero1\OpenPos\Api\Data\PaymentInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'zero1_openpos_payment';

    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init(\Zero1\OpenPos\Model\ResourceModel\Payment::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
