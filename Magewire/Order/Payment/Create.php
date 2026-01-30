<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire\Order\Payment;

use Magewirephp\Magewire\Component;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\Payment\MethodProvider;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\RequestInterface as CoreRequest;
use Zero1\OpenPos\Model\UrlProvider;
use Magento\Sales\Api\Data\OrderInterface;

class Create extends Component
{
    public string $orderId = '';
    public string $paymentMethod = '';
    public string $paymentType = '';
    public $amount = 0.00;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var MethodProvider
     */
    protected $methodProvider;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CoreRequest
     */
    protected $coreRequest;

    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param MethodProvider $methodProvider
     * @param OrderManagement $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param CoreRequest $coreRequest
     * @param UrlProvider $urlProvider
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        MethodProvider $methodProvider,
        OrderManagement $orderManagement,
        OrderRepositoryInterface $orderRepository,
        CoreRequest $coreRequest,
        UrlProvider $urlProvider
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->methodProvider = $methodProvider;
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->coreRequest = $coreRequest;
        $this->urlProvider = $urlProvider;
    }

    /**
     * Ensure order ID is set for subsequent Magewire requests.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->orderId = (string)$this->coreRequest->getParam('id');
    }

    /**
     * Return current order being viewed.
     *
     * @return OrderInterface
     */
    protected function getOrder(): OrderInterface
    {
        return $this->orderRepository->get($this->orderId);
    }

    /**
     * Return total paid so far for the current order.
     *
     * @return float
     */
    public function getTotalPaid(): float
    {
        return $this->orderManagement->getTotalPaid($this->getOrder());
    }

    /**
     * Return total remaining to pay for the current order.
     *
     * @return float
     */
    public function getTotalRemaining(): float
    {
        return $this->orderManagement->getTotalRemaining($this->getOrder());
    }

    /**
     * Check if the current order is fully paid.
     *
     * @return boolean
     */
    public function isOrderPaid(): bool
    {
        return $this->orderManagement->isOrderPaid($this->getOrder());
    }

    /**
     * Get all OpenPOS payment methods currently available.
     *
     * @return array
     */
    public function getAvailablePaymentMethods(): array
    {
        $paymentMethods = [];

        $allPaymentMethods = $this->methodProvider->getAll();
        foreach($allPaymentMethods as $paymentMethod) {
            if(isset($paymentMethod['canUseForLayaways']) && $paymentMethod['canUseForLayaways'] == true) {
                $paymentMethods[$paymentMethod['code']] = $paymentMethod['label'];
            }
        }

        return $paymentMethods;
    }

    /**
     * Get all payments for an order, convert into array for Magewire.
     *
     * @return array
     */
    public function getPayments(): array
    {
        $payments = [];
        $order = $this->getOrder();

        // @todo this might be returning duplicate records, check.
        $orderPayments = $this->orderManagement->getPaymentsForOrder($order);
        foreach ($orderPayments as $orderPayment) {
            $payments[] = [
                'id' => $orderPayment->getId(),
                'admin_user' => $orderPayment->getAdminUser(),
                'amount' => $orderPayment->getBasePaymentAmount(),
                'tax_amount' => $orderPayment->getBaseTaxAmount(),
                'payment_method' => $orderPayment->getPaymentMethod(),
                'created_at' => $orderPayment->getCreatedAt(),
            ];
        }

        return $payments;
    }

    /**
     * Validate form input and create a payment.
     *
     * @return void
     */
    public function startPayment(): void
    {
        if ($this->paymentType === 'remaining') {
            $this->amount = $this->getTotalRemaining();
        }

        // Validation
        if (empty($this->paymentMethod)) {
            $this->dispatchErrorMessage('Please select a payment method.');
            return;
        }

        if ($this->amount <= 0) {
            $this->dispatchErrorMessage('Payment amount must be greater than 0.');
            return;
        }

        if ($this->amount > $this->getTotalRemaining()) {
            $this->dispatchErrorMessage('Payment amount is greater than the remaining balance.');
            return;
        }

        $order = $this->getOrder();

        try {
            $payment = $this->orderManagement->makePayment($order, $this->amount, 'openpos_layaways', $this->paymentMethod);

            if($payment->getId()) {
                $this->dispatchSuccessMessage('Payment was successful.');
            } else {
                $this->dispatchErrorMessage(__('An unknown error occurred while saving the payment.'));
            }
        } catch(\Exception $e) {
            $this->dispatchErrorMessage(__('An error occurred while saving the payment: %1', $e->getMessage()));
        }

        $this->redirect($this->urlProvider->getOrderViewUrl($order));
    }
}