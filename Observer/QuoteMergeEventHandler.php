<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Session\Generic as CoreSession;

class QuoteMergeEventHandler implements ObserverInterface
{
    public $trackingHelper;
    public $coreSession;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        CoreSession $coreSession
    ) {
        $this->_trackingHelper = $trackingHelper;
        $this->_coreSession = $coreSession;
        $this->_logger = $context->getLogger();
        parent::__construct($context);
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
                $this->_logger->critical($e);
            }
        }
    }
}
