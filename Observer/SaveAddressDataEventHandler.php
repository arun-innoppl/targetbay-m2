<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveAddressDataEventHandler implements ObserverInterface
{
    //const ANONYMOUS_USER = 'anonymous';
    //const ALL_PAGES = 'all';
    //const PAGE_VISIT = 'page-visit';
    //const PAGE_REFERRAL = 'referrer';
    const ONESTEPCHECKOUT_ADDRESS = 'onestepcheckout';
    const BILLING = 'billing';
    const SHIPPING = 'shipping';

    protected $_trackingHelper;
    protected $_checkoutSession;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Targetbay\Tracking\Helper\Data $_trackingHelper
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_checkoutSession = $_checkoutSession;
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
     * @return bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::ONESTEPCHECKOUT_ADDRESS)) {
            return false;
        }

        $this->_checkoutSession->setTitle('Checkout');
        $quote = $this->_checkoutSession->getQuote();

        $billingInfo = $this->_trackingHelper->getAddressData($quote, self::BILLING);
        $this->pushPages($billingInfo, self::BILLING);

        $shippingInfo = $this->_trackingHelper->getAddressData($quote, self::SHIPPING);
        $this->pushPages($shippingInfo, self::SHIPPING);
    }
}
