<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteMergeEventHandler implements ObserverInterface
{
    protected $_trackingHelper;
    protected $_coreSession;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\Session\Generic $_coreSession
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_coreSession = $_coreSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $abandonedMail = $this->_coreSession->getAbandonedMail();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        if ($abandonedMail) {
            try {
                $this->_trackingHelper->debug('MergeObs');
                $cart->truncate();
            } catch (\Exception $e) {
                $this->_trackingHelper->debug('Error:' . $e->getMessage());
                $objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
        }
    }
}
