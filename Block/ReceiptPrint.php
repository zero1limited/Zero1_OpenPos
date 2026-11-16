<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Theme\Block\Html\Header\Logo as LogoBlock;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\ScopeInterface;

class ReceiptPrint extends Template
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var LogoBlock
     */
    protected $logoBlock;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Customer facing payment method names, keyed by payment method code.
     * Methods not listed use their Magento title.
     *
     * @var array
     */
    protected $paymentMethodLabels;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var array|null
     */
    protected $payments;

    /**
     * @param Context $context
     * @param OpenPosConfiguration $openPosConfiguration
     * @param CheckoutSession $checkoutSession
     * @param OrderManagement $orderManagement
     * @param LogoBlock $logoBlock
     * @param TimezoneInterface $timezone
     * @param array $paymentMethodLabels
     * @param array $data
     */
    public function __construct(
        Context $context,
        OpenPosConfiguration $openPosConfiguration,
        CheckoutSession $checkoutSession,
        OrderManagement $orderManagement,
        LogoBlock $logoBlock,
        TimezoneInterface $timezone,
        array $paymentMethodLabels = [],
        array $data = []
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->checkoutSession = $checkoutSession;
        $this->orderManagement = $orderManagement;
        $this->logoBlock = $logoBlock;
        $this->timezone = $timezone;
        $this->paymentMethodLabels = $paymentMethodLabels;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getReceiptHeaderContents(): string
    {
        return $this->formatReceiptText($this->openPosConfiguration->getReceiptHeader());
    }

    /**
     * @return string
     */
    public function getReceiptFooterContents(): string
    {
        return $this->formatReceiptText($this->openPosConfiguration->getReceiptFooter());
    }

    /**
     * Convert plain text line breaks to <br />, so multi-line text pasted into config renders as entered.
     * Content that already uses HTML line breaks / block elements is left untouched to avoid double spacing.
     *
     * @param string|null $contents
     * @return string
     */
    protected function formatReceiptText(?string $contents): string
    {
        $contents = trim((string)$contents);

        if ($contents === '' || preg_match('/<(br|p|div|ul|ol|li|table|tr|h[1-6])\b/i', $contents)) {
            return $contents;
        }

        return nl2br($contents);
    }

    /**
     * @return bool
     */
    public function isLogoEnabled(): bool
    {
        return $this->openPosConfiguration->isReceiptLogoEnabled() && $this->getLogoUrl() !== '';
    }

    /**
     * Get logo URL
     *
     * @return string
     */
    public function getLogoUrl(): string
    {
        return (string)$this->logoBlock->getLogoSrc();
    }

    /**
     * @return string
     */
    public function getLogoAlt(): string
    {
        return (string)$this->logoBlock->getLogoAlt();
    }

    /**
     * @return string
     */
    public function getOrderIncrementId(): string
    {
        return (string)$this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->timezone->formatDateTime($this->getOrder()->getCreatedAt());
    }

    /**
     * Name of the order's payment method.
     * The receipt lists the payments actually taken, see getPayments().
     *
     * @deprecated For layaway orders this is the layaway method, not how the customer paid.
     * @see getPayments()
     * @return string
     */
    public function getOrderPayment(): string
    {
        return $this->getPaymentMethodLabel((string)$this->getOrder()->getPayment()->getMethod());
    }

    /**
     * @return array
     */
    public function getOrderItems(): array
    {
        return $this->getOrder()->getAllVisibleItems();
    }

    /**
     * Quantity without trailing zeros, so decimal quantities (e.g. 1.5) aren't truncated.
     *
     * @param OrderItem $orderItem
     * @return string
     */
    public function getItemQty(OrderItem $orderItem): string
    {
        return rtrim(rtrim(number_format((float)$orderItem->getQtyOrdered(), 4, '.', ''), '0'), '.');
    }

    /**
     * Line total including tax, before discount. Discounts are shown as their own total.
     *
     * @param OrderItem $orderItem
     * @return string
     */
    public function getItemRowTotal(OrderItem $orderItem): string
    {
        return $this->formatPrice($orderItem->getRowTotalInclTax());
    }

    /**
     * @return string
     */
    public function getSubtotal(): string
    {
        return $this->formatPrice($this->getOrder()->getSubtotalInclTax());
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return abs((float)$this->getOrder()->getDiscountAmount()) > 0.0001;
    }

    /**
     * @return string
     */
    public function getDiscountLabel(): string
    {
        $description = (string)$this->getOrder()->getDiscountDescription();

        return $description !== ''
            ? (string)__('Discount (%1)', $description)
            : (string)__('Discount');
    }

    /**
     * @return string negative amount, e.g. "-£2.00"
     */
    public function getDiscountAmount(): string
    {
        return $this->formatPrice($this->getOrder()->getDiscountAmount());
    }

    /**
     * @return bool
     */
    public function hasShipping(): bool
    {
        return (float)$this->getOrder()->getShippingInclTax() > 0.0001;
    }

    /**
     * @return string
     */
    public function getShippingAmount(): string
    {
        return $this->formatPrice($this->getOrder()->getShippingInclTax());
    }

    /**
     * @return string
     */
    public function getOrderGrandTotal(): string
    {
        return $this->formatPrice($this->getOrder()->getGrandTotal());
    }

    /**
     * Payments taken for the order, e.g. each payment towards a layaway.
     *
     * @return array[] each with 'label' and 'amount' (formatted)
     */
    public function getPayments(): array
    {
        $payments = [];
        foreach ($this->getPaymentAmounts() as $payment) {
            $method = $this->getPaymentMethodLabel($payment['method']);
            $payments[] = [
                'label' => $payment['amount'] < 0
                    ? (string)__('Refunded by %1', $method)
                    : (string)__('Paid by %1', $method),
                'amount' => $this->formatPrice($payment['amount'])
            ];
        }

        return $payments;
    }

    /**
     * @return string
     */
    public function getTotalPaid(): string
    {
        return $this->formatPrice($this->getTotalPaidAmount());
    }

    /**
     * Whether part of the order is still to be paid, e.g. a layaway.
     *
     * @return bool
     */
    public function hasBalanceDue(): bool
    {
        return $this->getBalanceDueAmount() > 0.0001;
    }

    /**
     * @return string
     */
    public function getBalanceDue(): string
    {
        return $this->formatPrice($this->getBalanceDueAmount());
    }

    /**
     * Format an amount in the order's currency.
     * Order amounts are already in that currency, so they mustn't be converted again.
     *
     * @param mixed $amount
     * @return string
     */
    public function formatPrice($amount): string
    {
        return (string)$this->getOrder()->formatPriceTxt((float)$amount);
    }

    /**
     * Customer facing name of a payment method.
     *
     * @param string $methodCode
     * @return string
     */
    public function getPaymentMethodLabel(string $methodCode): string
    {
        if (isset($this->paymentMethodLabels[$methodCode])) {
            return (string)__($this->paymentMethodLabels[$methodCode]);
        }

        $title = (string)$this->_scopeConfig->getValue(
            'payment/' . $methodCode . '/title',
            ScopeInterface::SCOPE_STORE,
            $this->getOrder()->getStoreId()
        );

        return $title !== '' ? (string)__($title) : $methodCode;
    }

    /**
     * Payments recorded by OpenPOS. Orders placed on the POS store outside of a till (e.g. the Magento admin)
     * have none, so fall back to Magento's record of what has been paid.
     *
     * @return array[] each with 'method' (code) and 'amount' (float)
     */
    protected function getPaymentAmounts(): array
    {
        if ($this->payments !== null) {
            return $this->payments;
        }

        $order = $this->getOrder();
        $this->payments = [];

        foreach ($this->orderManagement->getPaymentsForOrder($order) as $payment) {
            $this->payments[] = [
                'method' => (string)$payment->getPaymentMethod(),
                'amount' => (float)$payment->getBasePaymentAmount()
            ];
        }

        if (!$this->payments && abs((float)$order->getTotalPaid()) > 0.0001) {
            $this->payments[] = [
                'method' => (string)$order->getPayment()->getMethod(),
                'amount' => (float)$order->getTotalPaid()
            ];
        }

        return $this->payments;
    }

    /**
     * @return float
     */
    protected function getTotalPaidAmount(): float
    {
        return (float)array_sum(array_column($this->getPaymentAmounts(), 'amount'));
    }

    /**
     * @return float
     */
    protected function getBalanceDueAmount(): float
    {
        return round((float)$this->getOrder()->getGrandTotal() - $this->getTotalPaidAmount(), 4);
    }

    /**
     * @return Order
     */
    protected function getOrder(): Order
    {
        if(!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
        }

        return $this->order;
    }
}
