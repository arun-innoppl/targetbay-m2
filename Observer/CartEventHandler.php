<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class CartEventHandler implements ObserverInterface
{
    const CHECKOUT = 'checkout-cart';

    public $coreSession;

    public function __construct(
        \Magento\Framework\Session\Generic $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteId = $this->_coreSession->getRestoreQuoteId();
        $abandonedMail = $this->_coreSession->getAbandonedMail();
        if ($abandonedMail && $quoteId != '') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

            if ($customerSession->isLoggedIn()) {
                return false;
            }

            $quote = $objectManager->get('Magento\Quote\Model\Quote')->load($quoteId);
            $cart = $objectManager->get('Magento\Checkout\Model\Cart');
            $price = 0;

            $quoteItems = $quote->getAllVisibleItems();

            foreach ($quoteItems as $item) {
                $item->setCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
            $cart->saveQuote();
            $cart->save();
        }
    }
}
