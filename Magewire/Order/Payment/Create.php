<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire\Order\Payment;

use Magewirephp\Magewire\Component;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Payments as OpenPosPaymentsHelper;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\RequestInterface as CoreRequest;
use Magento\Sales\Api\Data\OrderInterface;

class Create extends Component
{
    public string $orderId = '';
    public string $paymentMethod = '';
    public string $paymentType = '';
    public $amount = 0.00;

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosPaymentsHelper
     */
    protected $openPosPaymentsHelper;

    /**
     * @var OpenPosOrderHelper
     */
    protected $openPosOrderHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CoreRequest
     */
    protected $coreRequest;

    /**
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosPaymentsHelper $openPosPaymentsHelper
     * @param OpenPosOrderHelper $openPosOrderHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CoreRequest $coreRequest
     */
    public function __construct(
        OpenPosHelper $openPosHelper,
        OpenPosPaymentsHelper $openPosPaymentsHelper,
        OpenPosOrderHelper $openPosOrderHelper,
        OrderRepositoryInterface $orderRepository,
        CoreRequest $coreRequest
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->openPosPaymentsHelper = $openPosPaymentsHelper;
        $this->openPosOrderHelper = $openPosOrderHelper;
        $this->orderRepository = $orderRepository;
        $this->coreRequest = $coreRequest;
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
        return $this->openPosOrderHelper->getTotalPaid($this->getOrder());
    }

    /**
     * Return total remaining to pay for the current order.
     *
     * @return float
     */
    public function getTotalRemaining(): float
    {
        return $this->openPosOrderHelper->getTotalRemaining($this->getOrder());
    }

    /**
     * Check if the current order is fully paid.
     *
     * @return boolean
     */
    public function isOrderPaid(): bool
    {
        return $this->openPosOrderHelper->isOrderPaid($this->getOrder());
    }

    /**
     * Get all OpenPOS payment methods currently available.
     *
     * @return array
     */
    public function getAvailablePaymentMethods(): array
    {
        $paymentMethods = [];

        $allPaymentMethods = $this->openPosPaymentsHelper->getAll();
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
        $orderPayments = $this->openPosOrderHelper->getPaymentsForOrder($order);
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
            $payment = $this->openPosOrderHelper->makePayment($order, $this->amount, 'openpos_layaways', $this->paymentMethod);

            if($payment->getId()) {
                $this->dispatchSuccessMessage('Payment was successful.');
            } else {
                $this->dispatchErrorMessage(__('An unknown error occurred while saving the payment.'));
            }
        } catch(\Exception $e) {
            $this->dispatchErrorMessage(__('An error occurred while saving the payment: %1', $e->getMessage()));
        }

        $this->redirect($this->openPosHelper->getOrderViewUrl($order));
    }
}