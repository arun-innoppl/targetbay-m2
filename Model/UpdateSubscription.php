<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\UpdateSubscriptionInterface;

/**
 * Defines the implementaiton class of the UpdateSubscription.
 */
class UpdateSubscription implements UpdateSubscriptionInterface
{

    /**
     * @var \Magento\Framework\App\RequestInterface $request
     */
    public $request;

    /**
     * @var \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    public $trackingHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public $storeManager;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public $subscriberFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->_request = $request;
        $this->_trackingHelper = $trackingHelper;
        $this->_storeManager = $storeManager;
        $this->_customerRepository = $customerRepository;
        $this->_subscriberFactory = $subscriberFactory;
    }

    /**
     * update newsletter subscription
     *
     * @return boolean
     */
    public function updatesubscription()
    {
        $customerEmail = $this->_request->getParam('email');
        $subscriptionStatus = $this->_request->getParam('status');
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        // Customer id and email should not be empty, otherwise don't process this request.
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory');
        $customer = $customerFactory->create();
        $customer->setWebsiteId($websiteId)->loadByEmail($customerEmail);
        $customerId = $customer->getEntityId();
        if (empty($customerId) || empty($subscriptionStatus)) {
            return false;
        }

        try {
            $storeId = $this->_storeManager->getStore()->getId();
            $customerRepo = $this->_customerRepository->getById($customerId);
            $customerRepo->setStoreId($storeId);
            $this->_customerRepository->save($customerRepo);
            if ($subscriptionStatus == 1) {
                $this->_subscriberFactory->create()->subscribeCustomerById($customerId);
            } elseif ($subscriptionStatus == 2) {
                $this->_subscriberFactory->create()->unsubscribeCustomerById($customerId);
            } else {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            $this->_trackingHelper->debug("ERROR:" . $e->getMessage());
        }
    }
}
