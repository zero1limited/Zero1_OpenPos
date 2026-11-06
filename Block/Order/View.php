<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block\Order;

/**
 * Order view on the till. The core block links back to customer account routes (sales/order/history, or the guest
 * order form), resolved against the current route, which don't exist under openpos/order.
 */
class View extends \Magento\Sales\Block\Order\View
{
    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('openpos/order/index');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getBackTitle()
    {
        return __('View all orders');
    }
}
