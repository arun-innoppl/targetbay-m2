<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\OrderRepoInterface;

/**
 * Defines the implementation class of the OrderRepoInterface.
 */
class OrderRepo implements OrderRepoInterface
{
    const BILLING = 'billing';
    const SHIPPING = 'shipping';

    /**
     * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
     */
    protected $_trackingHelper;

    /**
     * @param \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_trackingHelper = $trackingHelper;
    }

    /**
     * Get the list of orders
     *
     * @return OrderInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $dateFormat = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $collection = $orderCollection->create()->addFieldToSelect('*');

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $ordersData = [];
        
        $timezone = $scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        foreach ($collection->getItems() as $order) {
            $ordersData [$order->getId()] = $order->toArray();
            $ordersData [$order->getId()] [self::BILLING] = $this->_trackingHelper->getAddressData($order, self::BILLING);
            if ($order->getShippingAddress()) {
                $ordersData [$order->getId()] [self::SHIPPING] = $this->_trackingHelper->getAddressData($order, self::SHIPPING);
                if ($order->getStatus() == self::ORDER_COMPLETE) {
                    //order shipped date
                    foreach ($order->getShipmentsCollection() as $shipment) {
                        /** @var $shipment Mage_Sales_Model_Order_Shipment */
                        $shipmentDate = $dateFormat->timestamp(strtotime($shipment->getCreatedAt()));
                        $ordersData[$order->getId()]['shipped_at'] = date('F j, Y g:i a',strtotime($actualDate." UTC"));
                        $ordersData[$order->getId()]['timezone'] = $timezone;
                    }
                }
            } else {
                $ordersData [$order->getId()] [self::SHIPPING] = '';
            }
            $ordersData [$order->getId()] ['cart_items'] = $this->_trackingHelper->getOrderItemsInfo($order, true);
        }

        return $ordersData;
    }
}
