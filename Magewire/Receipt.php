<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Framework\Validator\EmailAddress;
use Magento\Sales\Model\Order;

class Receipt extends Component
{
    public $listeners = ['print', 'email'];

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var string
     */
    public $emailInput = '';

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository,
        OrderSender $orderSender,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        // Prefill email input with customers email if logged in
        if($this->customerSession->getCustomerId() && $this->emailInput === '') {
            $this->emailInput = $this->customerSession->getCustomer()->getEmail();
        }
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Perform a print from browser / operating system.
     * 
     * @return void
     */
    public function print(): void
    {
        $this->dispatchBrowserEvent('js-print');
    }

    /**
     * Email a reciept / Magento order email to specified email address.
     * 
     * @return void
     */
    public function email(): void
    {
        if (!ValidatorChain::is($this->emailInput, EmailAddress::class)) {
            $this->dispatchErrorMessage(__('Please enter a valid email address.'));
            return;
        }

        $order = $this->orderRepository->get($this->getOrder()->getId());

        $order->setCustomerId(null);
        $order->setCustomerIsGuest(true);
        $order->setCustomerEmail($this->emailInput);

        $this->orderRepository->save($order);
        $this->orderSender->send($order, true);
    }

    /**
     * Start new order - log out of current customer and redirect to homepage.
     * 
     * @return void
     */
    public function newOrder(): void
    {
        $this->customerSession->setCustomerId(null);
        $this->redirect('/');
    }
}
