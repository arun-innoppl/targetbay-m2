<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class SaveAccountDataEventHandler implements ObserverInterface
{
    const CUSTOMER_ACCOUNT = 'change-user-account-info';

    public $trackingHelper;
    public $checkoutSession;
    public $customerSession;
    public $date;

    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_trackingHelper = $trackingHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->_apiToken = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName = $this->_trackingHelper->getApiIndex();
        $this->_tbHost = $this->_trackingHelper->getHostname();
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
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    /**
     * Update account info observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void|bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->_trackingHelper->canTrackPages(self::CUSTOMER_ACCOUNT)) {
            return false;
        }
        $this->_checkoutSession->setTitle('Account Information');
        $data = $this->_trackingHelper->visitInfo();
        $customerId = $this->_customerSession->getCustomerId();
        $customerObj = $objectManager->get('Magento\Customer\Model\Customer');
        $data['customer_id'] = $customerId;
        $data['firstname'] = $customerObj->getFirstname();
        $data['lastname'] = $customerObj->getLastname();
        $data['email'] = $customerObj->getEmail();
        $data['account_updated'] = $this->_date->date('Y-m-d');
        $this->pushPages($data, self::CUSTOMER_ACCOUNT);
    }
}
