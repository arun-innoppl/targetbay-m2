<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class UserLoginEventHandler implements ObserverInterface
{
    //const ANONYMOUS_USER = 'anonymous';
    //const ALL_PAGES = 'all';
    //const PAGE_VISIT = 'page-visit';
    //const PAGE_REFERRAL = 'referrer';
    const LOGIN = 'login';

    protected $_trackingHelper;
    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper
    ) {
        $this->_trackingHelper = $_trackingHelper;
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::LOGIN)) {
            $this->_trackingHelper->debug("canTrackPages");
            return false;
        }
        if (!$observer->getCustomer()) {
            $this->_trackingHelper->debug("observer");
            return false;
        }

        $data = $this->_trackingHelper->getCustomerData($observer->getCustomer(), self::LOGIN);
        $this->pushPages($data, self::LOGIN);
    }
}
