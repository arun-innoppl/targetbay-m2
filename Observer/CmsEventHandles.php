<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class EventHandles
 *
 * Custom handles to the page
 *
 * @package Targetbay\Tracking\Observer
 */
class CmsEventHandles implements ObserverInterface
{
    const PAGE_VISIT = 'page-visit';
    const PAGE_REFERRAL = 'referrer';

    private $apiToken;
    private $indexName;
    private $tbHost;

    /**
     * @var \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    protected $trackingHelper;

    /**
     * @param \Targetbay\Tracking\Helper\Data $trackingHelper
     */
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
     * @param unknown $data
     * @param unknown $type
     */
    public function pushPages($data, $type)
    {
        $endPointUrl = $this->_tbHost . $type . $this->_apiToken;
        $data['index_name'] = $this->_indexName;
        try {
            $res = $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    public function pushReferralData()
    {
        if (! $this->_trackingHelper->canTrackPages(self::PAGE_REFERRAL)) {
            return false;
        }
        if ($referrerData = $this->_trackingHelper->getRefererData()) {
            $this->pushPages($referrerData, self::PAGE_REFERRAL);
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');

        if (! $this->_trackingHelper->canTrackPages(self::PAGE_VISIT)) {
            return false;
        }

        // Page referrer Tracking
        $this->pushReferralData();

        $data = $this->_trackingHelper->visitInfo();

        $moduleName     = $requestInterface->getModuleName();

        if ($moduleName == 'cms' || $moduleName == 'brand') {
            return false;
        }
        
        // Page Visit Tracking
        $this->pushPages($data, self::PAGE_VISIT);
    }
}
