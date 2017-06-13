<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class UserLogoutEventHandler implements ObserverInterface
{
    const LOGOUT = 'logout';

    private $trackingHelper;
    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_trackingHelper = $trackingHelper;
        $this->_apiToken = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName = $this->_trackingHelper->getApiIndex();
        $this->_tbHost = $this->_trackingHelper->getHostname();
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::LOGOUT)) {
            return false;
        }
        $data = $this->_trackingHelper->getCustomerData($observer->getCustomer(), self::LOGOUT);
        $this->pushPages($data, self::LOGOUT);

        // Remove all Cookies
        $this->_trackingHelper->removeCookies();
    }

    /**
     * API Calls
     *
     * @param unknown $data
     * @param unknown $type
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
}
