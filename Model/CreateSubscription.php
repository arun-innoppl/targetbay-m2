<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CreateSubscriptionInterface;

/**
 * Defines the implementation class of the CreateSubscription.
 */
class CreateSubscription implements CreateSubscriptionInterface
{
    const STATUS_SUBSCRIBED = 1;

    /**
     * @var \Magento\Framework\App\RequestInterface $_request
     */
    protected $_request;

    /**
     * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
     */
    protected $_trackingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory $_subscriberFactory
     */
    protected $_subscriberFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $_request,
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Newsletter\Model\SubscriberFactory $_subscriberFactory
    ) {
        $this->_request = $_request;
        $this->_trackingHelper = $_trackingHelper;
        $this->_storeManager = $_storeManager;
        $this->_subscriberFactory = $_subscriberFactory;
        $this->_customerSession = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * create new newsletter subscription.
     * @throws \Exception
     * @return int
     */
    public function createsubscription()
    {
        $email = $this->_request->getParam('email');
        $message = [];

        $message['message'] = $this->validateEmailFormat($email);
        $message['message'] = $this->validateEmailAvailable($email);
        $message['message'] = $this->validateAlreadySuscribed($email);
        try {
            if (isset($message)) {
                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status) {
                    $message['message'] = 'Thank you for your subscription.';
                }
            }
            return $message;
        } catch (\Exception $e) {
            $this->_trackingHelper->debug($e->getMessage());
        }
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailAvailable($email)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This email address is already assigned to another user.')
            );
        }
    }

    /**
     * Validates that the email address already subscribed
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateAlreadySuscribed($email)
    {
        $objectmanager = \Magento\Framework\App\ObjectManager::getInstance();
        $subscriber = $objectmanager->get('\Magento\Newsletter\Model\Subscriber');

        $message = '';
        $subscribedUser = $subscriber->loadByEmail($email);

        if ($subscribedUser->isSubscribed()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This email address is already subscribed.')
            );
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailFormat($email)
    {
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid email address.'));
        }
    }
}
