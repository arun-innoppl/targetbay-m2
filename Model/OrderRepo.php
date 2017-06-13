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
    const ORDER_COMPLETE = 'complete';

    /**
     * @var \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    public $trackingHelper;

    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     */
    public $countryInformation;

    /**
     * @param \Targetbay\Tracking\Helper\Data $trackingHelper
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     */
    public function __construct(
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_trackingHelper = $trackingHelper;
        $this->_countryInformation = $countryInformation;
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
            $ordersData [$order->getId()] [self::BILLING] = $this->getUserAddressData($order, self::BILLING);
            if ($order->getShippingAddress()) {
                $ordersData [$order->getId()] [self::SHIPPING] = $this->getUserAddressData($order, self::SHIPPING);
                if ($order->getStatus() == self::ORDER_COMPLETE) {
                    //order shipped date
                    foreach ($order->getShipmentsCollection() as $shipment) {
                        /** @var $shipment Mage_Sales_Model_Order_Shipment */
                        $ordersData[$order->getId()]['shipped_at'] = $shipment->getCreatedAt();
                        $ordersData[$order->getId()]['timezone'] = $timezone;
                    }
                }
            } else {
                $ordersData[$order->getId()][self::SHIPPING] = '';
            }
            $ordersData [$order->getId()]['payment_method'] = $order->getPayment()->getMethodInstance()->getTitle();
            $ordersData [$order->getId()] ['cart_items'] = $this->_trackingHelper->getOrderItemsInfo($order, true);
        }

        return $ordersData;
    }

    public function getUserAddressData($object, $type)
    {
        $address = ($type === self::SHIPPING) ? $object->getShippingAddress() : $object->getBillingAddress();
        $addressData['first_name'] = $address->getFirstname();
        $addressData['last_name'] = $address->getLastname();
        $guestUsername = $address->getFirstname() . ' ' . $address->getLastname();
        $gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
        $addressData['user_name'] = $object->getCustomerIsGuest() ? $gName : $addressData['first_name'] . ' ' . $addressData['last_name'];
        $addressData['order_id'] = $object->getId();
        $addressData['user_mail'] = $object->getCustomerEmail();
        $addressData['address1'] = $address->getStreet(1);
        $addressData['address2'] = $address->getStreet(2);
        $addressData['city'] = $address->getCity();
        $addressData['state'] = $address->getRegion();
        $addressData['zipcode'] = $address->getPostcode();

        $countryName = '';
        if ($address->getCountryId()) {
            $countryName = $this->_countryInformation->getCountryInfo($address->getCountryId())->getFullNameLocale();
        }

        $addressData['country'] = $countryName !== '' ? $countryName : $address->getCountryId();
        $addressData['phone'] = $address->getTelephone();

        return $addressData;
    }
}
