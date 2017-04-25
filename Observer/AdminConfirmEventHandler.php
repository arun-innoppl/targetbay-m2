<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminConfirmEventHandler implements ObserverInterface
{
    const ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';

    protected $_trackingHelper;
    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper
    ) {
        $this->_trackingHelper  = $_trackingHelper;
        $this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName       = $this->_trackingHelper->getApiIndex();
        $this->_tbHost   = $this->_trackingHelper->getHostname();
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
     * Registration observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer_info = $observer->getCustomer()->getData();
        $data = array_merge($this->_trackingHelper->visitInfo(), $customer_info);
        $this->pushPages($data, self::ADMIN_ACTIVATE_ACCOUNT);
    }
}
