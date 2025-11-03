<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;

class Actions extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Retrieves the ID of the current order.
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return (int)$this->getOrder()->getId();
    }

    /**
     * Retrieves the current order from the registry.
     *
     * @return OrderInterface
     */
    protected function getOrder(): OrderInterface
    {
        return $this->registry->registry('current_order');
    }
}
