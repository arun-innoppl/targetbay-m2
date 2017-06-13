<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class EmptyCartEventHandler implements ObserverInterface
{
    const REMOVECART = 'remove-to-cart';

    protected $trackingHelper;
    protected $checkoutSession;
    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        CheckoutSession $checkoutSession
    ) {
        $this->_trackingHelper  = $trackingHelper;
        $this->_checkoutSession = $checkoutSession;
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
        $data['index_name'] = $this->_indexName;
        try {
            $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventData = $observer->getEvent()->getData('update_cart_action');
        $items = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $this->removeCartItem($item);
        }
    }

    public function removeCartItem($item)
    {
        if (!$this->_trackingHelper->canTrackPages(self::REMOVECART)) {
            return false;
        }
        $data = array_merge($this->_trackingHelper->getCartInfo(), $this->_trackingHelper->getItemInfo($item));
        $this->pushPages($data, self::REMOVECART);
        return true;
    }
}
