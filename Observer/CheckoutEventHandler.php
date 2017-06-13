<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutEventHandler implements ObserverInterface
{
    const CHECKOUT = 'checkout';

    public $trackingHelper;

    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_trackingHelper  = $trackingHelper;
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
        if (!empty($data)) {
            $data ['index_name'] = $this->_indexName;
            try {
                $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
            } catch (\Exception $e) {
                $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
            }
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::CHECKOUT)) {
            return false;
        }
        $data = $this->_trackingHelper->visitInfo();
        $this->pushPages($data, self::CHECKOUT);
    }
}
