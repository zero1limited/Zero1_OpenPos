<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Order\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;

class Create extends Template
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
    }

    /**
     * Retrieves the ID of the current order.
     *
     * @return int
     */
    public function getOrderId(): ?string
    {
        return $this->request->getParam('id');
    }
}