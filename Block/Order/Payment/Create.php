<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Order\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;

/**
 * WORK IN PROGRESS
 */

class Create extends Template
{
    protected RequestInterface $request;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
    }

    public function getOrderId(): ?string
    {
        return $this->request->getParam('id');
    }
}