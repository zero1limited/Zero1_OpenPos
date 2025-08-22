<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\ResourceModel;

class Payment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('openpos_payment', 'payment_id');
    }
}
