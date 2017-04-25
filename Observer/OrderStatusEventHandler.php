<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderStatusEventHandler implements ObserverInterface
{
    const ANONYMOUS_USER = 'anonymous';
    const ORDER_STATUS = 'order-status';

    protected $_request;
    protected $_trackingHelper;
    protected $_registry;
    private $_apiToken;
    private $_indexName;
    private $_tbHost;
    private $_logger;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\App\RequestInterface $_request,
        \Psr\Log\LoggerInterface $_logger,
        \Magento\Framework\Registry $_registry
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_request = $_request;
        $this->_registry = $_registry;
        $this->_apiToken = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName = $this->_trackingHelper->getApiIndex();
        $this->_tbHost = $this->_trackingHelper->getHostname();
        $this->_logger = $logger;
    }

    /**
     * API Calls
     *
     * @param $data
     * @param $type
     */
    public function pushPages($data, $type)
    {
        $endPointUrl = $this->_tbHost . $type . $this->_apiToken;
        $data ['index_name'] = $this->_indexName;
        try {
            $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Order data
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void|boolean
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$orderInfo = array();

        if (!$this->_trackingHelper->canTrackPages(self::ORDER_STATUS)) {
            return false;
        }
        try {
            $order = $observer->getEvent()->getOrder();
            $data = $this->_trackingHelper->getSessionInfo($order);
            if ($order->getCustomerIsGuest()) {
                $billingAddress = $order->getBillingAddress();
                $data ['first_name'] = $billingAddress->getFirstname();
                $data ['last_name'] = $billingAddress->getLastname();
                $guestUsername = $data ['first_name'] . ' ' . $data ['last_name'];
            } else {
                $data ['first_name'] = $order->getCustomerFirstname();
                $data ['last_name'] = $order->getCustomerLastname();
                $guestUsername = '';
            }

            $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
            $data ['user_name'] = $order->getCustomerIsGuest() ? $gName : $data ['first_name'] . ' ' . $data ['last_name'];
            $data ['user_mail'] = $order->getCustomerEmail();
            $data ['order_id'] = $order->getId();
            $data ['payment_title'] = $order->getPayment()->getMethodInstance()->getTitle();
            $data ['status'] = $order->getStatus();
            $this->pushPages($data, self::ORDER_STATUS);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
}
