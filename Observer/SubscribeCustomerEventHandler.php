<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class SubscribeCustomerEventHandler implements ObserverInterface
{
    //const ANONYMOUS_USER = 'anonymous';
    //const ALL_PAGES = 'all';
    //const PAGE_VISIT = 'page-visit';
    //const PAGE_REFERRAL = 'referrer';
    const SUBSCRIBE_CUSTOMER = 'user-subscribe';

    const STATUS_SUBSCRIBED = 1;
    const STATUS_NOT_ACTIVE = 2;
    const STATUS_UNSUBSCRIBED = 3;
    const STATUS_UNCONFIRMED = 4;

    protected $_trackingHelper;
    protected $_customerSession;
    protected $_request;
    protected $_storeManager;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Magento\Customer\Model\Session $_customerSession,
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\App\RequestInterface $_request
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_customerSession = $_customerSession;
        $this->_storeManager = $_storeManager;
        $this->_apiToken = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName = $this->_trackingHelper->getApiIndex();
        $this->_tbHost = $this->_trackingHelper->getHostname();
        $this->_request = $_request;
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::SUBSCRIBE_CUSTOMER)) {
            return false;
        }

        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerModel = $objectManager->get('Magento\Customer\Model\Customer');

        $this->_customerSession->setTitle('Newsletter Subscription');

        if ($this->_request->getParam('email')) {
            $email = $this->_request->getParam('email');
            $customer = $customerModel->setWebsiteId($websiteId)->loadByEmail($email);
            $customerId = $customer->getEntityId();
        } else {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $email = '';
        }

        $data = $this->_trackingHelper->visitInfo();

        if (empty($email)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$customer_data = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            $subscriberFactory = $objectManager->create('Magento\Newsletter\Model\Subscriber')
                ->loadByCustomerId($customerId);

            switch ($subscriberFactory->getSubscriberStatus()) {
                case self::STATUS_UNSUBSCRIBED:
                    $status = 'Unsubscribed';
                    break;
                case self::STATUS_SUBSCRIBED:
                    $status = 'Subscribed';
                    break;
                case self::STATUS_UNCONFIRMED:
                    $status = 'Unconfirmed';
                    break;
                case self::STATUS_NOT_ACTIVE:
                    $status = 'Not Activated';
                    break;
                default:
                    $status = $subscriberFactory->getSubscriberStatus();
                    break;
            }
        } else {
            $status = '';
        }

        $status = !empty($email) ? 'Subscribed' : $status;
        $data['user_mail'] = $this->_customerSession->isLoggedIn() ? $this->_customerSession->getCustomer()->getEmail() : $email;
        $data['subscription_status'] = $status;
        $this->pushPages($data, self::SUBSCRIBE_CUSTOMER);
    }
}
