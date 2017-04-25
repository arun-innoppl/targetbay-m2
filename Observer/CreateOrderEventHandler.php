<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreateOrderEventHandler implements ObserverInterface
{
    const ORDER_ITEMS = 'ordered-items';

    // order fullfillment process
    const ORDER_SHIPMENT = 'shipment';

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
        $this->_trackingHelper  = $_trackingHelper;
        $this->_request = $_request;
        $this->_registry = $_registry;
        $this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName       = $this->_trackingHelper->getApiIndex();
        $this->_tbHost   = $this->_trackingHelper->getHostname();
        $this->_logger = $_logger;
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
            $res = $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $orderInfo = [];

        if (!$this->_registry->registry('order_pushed')) {
            $this->_registry->register('order_pushed', true);
            if (!$this->_trackingHelper->canTrackPages(self::ORDER_ITEMS)) {
                return false;
            }

            try {
                $order = $observer->getEvent()->getOrder();
                $params = $this->_request->getParams();

                $order_id = $order->getIncrementId();
                $order_details = $objectManager->get('Magento\Sales\Model\Order');
                $order_information = $order_details->loadByIncrementId($order_id);

                if ($this->pushShipmentData($order_information, $params)) {
                    return false;
                } // order shipment process so no need to make order submit api.

                // Captute the customer registration.
                if ($customer = $this->_trackingHelper->isRegisterCheckout($order)) {
                    $this->pushRegisterData($customer);
                }

                // Order Data Push to the Tag Manager
                $orderInfo = $this->_trackingHelper->getInfo($order);
                $orderInfo['cart_items'] = $this->_trackingHelper->getOrderItemsInfo($order);
                $this->pushPages($orderInfo, self::ORDER_ITEMS);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        return;
    }

    public function pushShipmentData($order, $params)
    {
        if ($this->_trackingHelper->isFullFillmentProcess($params)) {
            try {
                $data = $this->_trackingHelper->getFullFillmentData($order, $params);
                $this->pushPages($data, self::ORDER_SHIPMENT);
                return true;
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return false;
    }

    public function pushRegisterData($customer)
    {
        if (! $this->_trackingHelper->canTrackPages(self::CREATE_ACCOUNT)) {
            return false;
        }
        try {
            $data = $this->_trackingHelper->getCustomerData($customer, self::CREATE_ACCOUNT);
            $this->pushPages($data, self::CREATE_ACCOUNT);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return;
    }
}
