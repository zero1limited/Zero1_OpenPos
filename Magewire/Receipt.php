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

class Receipt extends Component
{
    public $listeners = ['print', 'email'];

    public $emailInput = '';

    public $printMode = false;

    public function __construct(
        protected CheckoutSession $checkoutSession,
        protected CustomerSession $customerSession,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderSender $orderSender,
        protected OpenPosSessionHelper $openPosSessionHelper
    ) {}

    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function print()
    {
        $this->printMode = true;
        $this->dispatchBrowserEvent('js-print');
    }

    public function email()
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

    public function serveNewCustomer()
    {
        // TODO fix exception thrown when this is ran
        $customer = $this->openPosSessionHelper->getCustomerForAdminUser();
        $this->customerSession->setCustomerAsLoggedIn($customer);
        $this->redirect('/');
    }

    public function newOrder()
    {
        $this->redirect('/');
    }
}
