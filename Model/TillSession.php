<?php
namespace Zero1\OpenPos\Model;

/**
 * Class Problem
 */
class TillSession extends \Magento\Framework\Model\AbstractModel implements
    \Zero1\OpenPos\Api\Data\TillSessionInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'zero1_openpos_till_session';

    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init(\Zero1\OpenPos\Model\ResourceModel\TillSession::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
