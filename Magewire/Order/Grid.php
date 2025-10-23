<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire\Order;

use Magewirephp\Magewire\Component;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollectionFactory;

/**
 * This class couldn't extend Magewire Pagination class due to getAllPageItems risk of OOM'ing PHP.
 * Couldn't figure out how to mix the existing pagination methods with returning a limited order collection.
 * I have copied some of the Pagination methods here though and will revisit.
 */
class Grid extends Component
{
    public $listeners = [
        'toPreviousPage',
        'toNextPage'
    ];

    public $page = 1;
    public $pageSize = 10;
    public $totalPages;
    public $filterOrderId;
    public $filterCustomerEmail;
    public $filterStatus;

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosOrderHelper
     */
    protected $openPosOrderHelper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderStatusCollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosOrderHelper $openPosOrderHelper
     * @param CustomerSession $customerSession
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderStatusCollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(
        OpenPosHelper $openPosHelper,
        OpenPosOrderHelper $openPosOrderHelper,
        CustomerSession $customerSession,
        OrderCollectionFactory $orderCollectionFactory,
        OrderStatusCollectionFactory $orderStatusCollectionFactory
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->openPosOrderHelper = $openPosOrderHelper;
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
    }

    /**
     * Functionality copied from Component\Pagination
     */
    public function toPreviousPage(): void
    {
        $this->toPage(max($this->getPage() - 1, 1));
    }

    /**
     * Base functionality copied from Component\Pagination
     */
    public function toNextPage(): void
    {
        $this->toPage($this->getPage() + 1);
    }

    /**
     * Functionality copied from Component\Pagination
     */
    public function toPage($page): void
    {
        $this->page = (int) $page;
    }

    /**
     * Base functionality copied from Component\Pagination
     */
    public function onFirstPage(): bool
    {
        return $this->page === 1;
    }

    /**
     * Base functionality copied from Component\Pagination
     */
    public function onLastPage(): bool
    {
        return $this->page == $this->totalPages;
    }

    /**
     * Load filtered collection of orders for current page.
     * 
     * @return array
     */
    public function getOrderCollection(): array
    {
        $orders = [];

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->setOrder('created_at', 'desc');
        $orderCollection->setPageSize($this->pageSize);
        $orderCollection->setCurPage($this->page);
        $orderCollection->getSelect()
            ->joinLeft(
                ['op' => $orderCollection->getTable('openpos_payment')],
                'main_table.entity_id = op.order_id',
                [
                    'openpos_total_paid' => new \Zend_Db_Expr('IFNULL(SUM(op.base_payment_amount),0)')
                ]
            )
            ->group('main_table.entity_id');
        
        // Only load orders from OpenPOS store
        $orderCollection->addFieldToFilter('store_id', $this->openPosHelper->getPosStoreId());

        // If customer session active, only show current customer orders
        if($this->customerSession->isLoggedIn()) {
            $orderCollection->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
        }

        // Filters
        if($this->filterOrderId) {
            $orderCollection->addFieldToFilter(
                'increment_id',
                ['like' => '%' . $this->filterOrderId . '%']
            );
        }

        // Only allow customer filtering when not logged into customer session
        if($this->filterCustomerEmail && !$this->customerSession->isLoggedIn()) {
            $orderCollection->addFieldToFilter(
                'customer_email',
                ['like' => '%' . $this->filterCustomerEmail . '%']
            );
        }

        if($this->filterStatus) {
            $orderCollection->addFieldToFilter('status', $this->filterStatus);
        }


        foreach($orderCollection as $order) {
            $openPosStatus = (float)$order->getData('openpos_total_paid') >= $order->getBaseGrandTotal()  ? 'Paid' : 'Payments outstanding';
            $orders[] = [
                'increment_id' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'magento_status' => $order->getStatus(),
                'openpos_total_paid' => (float)$order->getData('openpos_total_paid'),
                'openpos_status' => $openPosStatus,
                'customer_name' => $order->getCustomerName(),
                'grand_total' => $order->getGrandTotal(),
                'view_url' => $this->openPosHelper->getOrderViewUrl($order)
            ];
        }

        $this->totalPages = ceil($orderCollection->getSize() / $this->pageSize);
        return $orders;
    }

    /**
     * Get all available order statuses for filter.
     * 
     * @return array
     */
    public function getOrderStatuses(): array
    {
        $orderStatusCollection = $this->orderStatusCollectionFactory->create();
        return $orderStatusCollection->toOptionArray();
    }

    /**
     * Check if we are currently serving guest.
     * 
     * @return bool
     */
    public function isCurrentCustomerGuest(): bool
    {
        return $this->customerSession->isLoggedIn() === false;
    }

    /**
     * Reset page when filter updated.
     * 
     * @return string
     */
    public function updatedFilterOrderId(string $value): string
    {
        $this->page = 1;
        return $value;
    }

    /**
     * Reset page when filter updated.
     * 
     * @return string
     */
    public function updatedFilterCustomerEmail(string $value): string
    {
        $this->page = 1;
        return $value;
    }

    /**
     * Reset page when filter updated.
     * 
     * @return string
     */
    public function updatedFilterStatus(string $value): string
    {
        $this->page = 1;
        return $value;
    }
}
