<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Zero1\OpenPos\Model\ResourceModel\Payment\CollectionFactory as PaymentCollectionFactory;
use Zero1\OpenPos\Model\PaymentFactory;
use Zero1\OpenPos\Api\PaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Zero1\OpenPos\Api\Data\PaymentInterface;
use Zero1\OpenPos\Model\PaymentMethod\Layaways;

class Order extends AbstractHelper
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var PaymentCollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @var PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @var PaymentRepositoryInterface
     */
    protected $paymentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param Context $context
     * @param PosHelper $openPosHelper
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param PaymentCollectionFactory $paymentCollectionFactory
     * @param PaymentFactory $paymentFactory
     * @param PaymentRepositoryInterface $paymentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        OpenPosSessionHelper $openPosSessionHelper,
        PaymentCollectionFactory $paymentCollectionFactory,
        PaymentFactory $paymentFactory,
        PaymentRepositoryInterface $paymentRepository,
        OrderRepositoryInterface $orderRepository

    ) {
        $this->openPosHelper = $openPosHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->paymentFactory = $paymentFactory;
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
        
        parent::__construct($context);
    }

    /**
     * Add OpenPOS payment to an order.
     * If the order is fully paid after the payment, invoice the order.
     * 
     * @param OrderInterface $order
     * @param float $amount
     * @param string $basePaymentMethodCode
     * @param string $paymentMethodCode
     * @return PaymentInterface on success, null on failure
     */
    public function makePayment(OrderInterface $order, $amount, $basePaymentMethodCode, $paymentMethodCode): ?PaymentInterface
    {
        $orderId = $order->getId();
        $adminUser = $this->openPosSessionHelper->getAdminUserFromTillSession()->getUserName();

        $taxRate = $this->getTaxRateForOrder($order);
        $taxAmount = round($amount * ($taxRate / 100), 2);

        /** @var \Zero1\OpenPos\Model\Payment $payment */
        $payment = $this->paymentFactory->create();
        $payment->setOrderId($orderId);
        $payment->setAdminUser($adminUser);
        $payment->setBasePaymentAmount($amount);
        $payment->setBaseTaxAmount($taxAmount);
        $payment->setBasePaymentMethod($basePaymentMethodCode);
        $payment->setPaymentMethod($paymentMethodCode);

        $this->paymentRepository->save($payment);

        // Check if order can now be completed
        if($this->isOrderPaid($order)) {
            $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE);
            $order->setStatus('complete');
            $this->orderRepository->save($order);
        }
        
        return $payment;
    }

    /**
     * Check if an order is fully paid.
     *
     * @param OrderInterface $order
     * @return boolean
     */
    public function isOrderPaid(OrderInterface $order): bool
    {
        return $this->getTotalRemaining($order) === 0;
    }

    /**
     * Return total paid so far for an order.
     *
     * @param OrderInterface $order
     * @return float
     */
    public function getTotalPaid(OrderInterface $order): float
    {
        $totalPaid = 0.00;

        $payments = $this->getPaymentsForOrder($order);
        foreach ($payments as $payment) {
            $totalPaid += (float)$payment->getBasePaymentAmount();
        }

        return $totalPaid;
    }

    /**
     * Return total remaining to pay for an order.
     *
     * @return float
     */
    public function getTotalRemaining(OrderInterface $order): float
    {
        return max(0, (float)$order->getGrandTotal() - $this->getTotalPaid($order));
    }

    /**
     * Get all payments for a given order.
     * 
     * @param OrderInterface $order
     * @return PaymentInterface[] array of payments
     */
    public function getPaymentsForOrder(OrderInterface $order) //@todo add return type
    {
        $payments = $this->paymentCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getEntityId());

        return $payments->getItems();
    }

    /**
     * Check if an order can be edited (i.e. has no payments).
     * 
     * @param OrderInterface $order
     * @return bool
     */
    public function canEdit(OrderInterface $order): bool
    {
        $payments = $this->getPaymentsForOrder($order);
        if(count($payments) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if a payment can be made on an order.
     * Order has the be status 'pending' and use the layaway payment method.
     * 
     * @param OrderInterface $order
     * @return bool
     */
    public function canMakePayment(OrderInterface $order): bool
    {
        if(!$this->openPosHelper->isPosOrder($order)) {
            return false;
        }

        // @todo order status
        if($order->getStatus() === 'pending' && $order->getPayment()->getMethod() === Layaways::PAYMENT_METHOD_CODE) {
            return !$this->isOrderPaid($order);
        }

        return false;
    }

    /**
     * Get tax rate for an order.
     * 
     * @param OrderInterface $order
     * @return float tax rate as a percentage
     */
    protected function getTaxRateForOrder(OrderInterface $order): float
    {
        try {
            $subtotal = (float)$order->getBaseSubtotal();
            $taxAmount = (float)$order->getBaseTaxAmount();

            return ($subtotal > 0) ? ($taxAmount / $subtotal) * 100 : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }
}
