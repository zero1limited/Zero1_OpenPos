<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
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
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var string
     */
    public $emailInput = '';

    /**
     * @var bool
     */
    public $printMode = false;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository,
        OrderSender $orderSender,
        OpenPosSessionHelper $openPosSessionHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->openPosSessionHelper = $openPosSessionHelper;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Enable print mode on Magewire block
     * TODO: review functionality here
     * 
     * @return void
     */
    public function print(): void
    {
        $this->printMode = true;
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
            $this->dispatchErrorMessage('Please enter a valid email address.');
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
