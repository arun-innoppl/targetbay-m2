<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddWishlistEventHandler implements ObserverInterface
{
    const WISHLIST = 'wishlist';

    protected $_customerSession;
    protected $_trackingHelper;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Customer\Model\Session $_customerSession
    ) {
        $this->_trackingHelper  = $_trackingHelper;
        $this->_customerSession = $_customerSession;
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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::WISHLIST)) {
            return false;
        }
        $this->_customerSession->setTitle('My Wishlist');
        $wishListItems = $observer->getEvent()->getItems();
        //$item_info = array();
        foreach ($wishListItems as $item) {
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
            }

            $item_info = $this->_trackingHelper->getWishlistProductInfo($item->getData('product_id'));
            $data = array_merge($this->_trackingHelper->visitInfo(), $item_info);
            $data ['item_id'] = $item->getWishlistItemId();
            if ($customOptions = $this->_trackingHelper->getCustomOptionsInfo($item, null)) {
                $data ['attributes'] = $customOptions;
            }
            $this->pushPages($data, self::WISHLIST);
        }
    }
}
