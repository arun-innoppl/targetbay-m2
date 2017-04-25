<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCartEventHandler implements ObserverInterface
{
    const ADD_TO_CART = 'add-to-cart';

    protected $_cart;
    protected $_trackingHelper;
    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Checkout\Model\Cart $_cart
    ) {
        $this->_cart = $_cart;
        $this->_trackingHelper  = $_trackingHelper;
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
        $data['index_name'] = $this->_indexName;
        try {
            $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    /**
     * @param Observer $observer
     * @return bool
     */
    public function execute(Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::ADD_TO_CART)) {
            return false;
        }

        $productEventInfo = $observer->getEvent()->getData('product');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote = $objectManager->get('Magento\Checkout\Model\Cart')->getQuote();
        $item = $quote->getItemByProduct($productEventInfo);

        $data = array_merge(
            $this->_trackingHelper->getCartInfo(),
            $this->_trackingHelper->getItemInfo($item, self::ADD_TO_CART)
        );

        $data['price'] = $item->getProduct()->getFinalPrice();

        if ($customOptions = $this->_trackingHelper->getCustomOptionsInfo($item, null)) {
            $data['attributes'] = $customOptions;
        }
        $this->pushPages($data, self::ADD_TO_CART);
    }
}
