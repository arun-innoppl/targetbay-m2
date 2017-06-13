<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;

class UpdateWishlistEventHandler implements ObserverInterface
{
    const UPDATE_WISHLIST = 'update-wishlist';

    public $request;
    public $trackingHelper;
    public $customerSession;

    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Magento\Framework\App\RequestInterface $request,
        CustomerSession $customerSession
    ) {
        $this->_trackingHelper = $trackingHelper;
        $this->_request = $request;
        $this->_customerSession = $customerSession;

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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->_trackingHelper->canTrackPages(self::UPDATE_WISHLIST)) {
            return false;
        }

        $this->_customerSession->setTitle('My Wishlist');

        $wishlistId = $this->_request->getParam('wishlist_id');
        $wishlistDesc = $this->_request->getParam('description');
        $wishlistQty = $this->_request->getParam('qty');
        $data = $this->_trackingHelper->visitInfo();
        $items = [];
        $data ['wishlist_id'] = $wishlistId;

        foreach ($wishlistDesc as $id => $item) {
            $wishlistItem = $objectManager->get('\Magento\Wishlist\Model\Item')->load($id);
            $items[$id]['item_id'] = $id;
            $items[$id]['product_id'] = $wishlistItem->getProductId();
            $items[$id]['store_id'] = $wishlistItem->getStoreId();
            $items[$id]['description'] = $item;
            $items[$id]['qty'] = $wishlistQty[$id];
        }

        $data['wishlist_items'] = $items;
        $this->pushPages($data, self::UPDATE_WISHLIST);
    }
}
