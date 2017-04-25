<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class DeleteWishlistEventHandler implements ObserverInterface
{
    const REMOVE_WISHLIST = 'remove-wishlist';

    protected $_request;
    protected $_trackingHelper;
    protected $_itemFactory;
    protected $_wishlistFactory;
    protected $_customerSession;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\App\RequestInterface $_request,
        \Magento\Wishlist\Model\ItemFactory $_itemFactory,
        \Magento\Wishlist\Model\WishlistFactory $_wishlistFactory,
        \Magento\Customer\Model\Session $_customerSession
    ) {
        $this->_trackingHelper  = $_trackingHelper;
        $this->_request = $_request;
        $this->_itemFactory = $_itemFactory;
        $this->_wishlistFactory = $_wishlistFactory;
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

    /**
     * Capture the remove cart item data
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->_trackingHelper->canTrackPages(self::REMOVE_WISHLIST)) {
            return false;
        }

        $this->_customerSession->setTitle('My Wishlist');
        $itemId = (int) $this->_request->getParam('item');
        $item = $this->_itemFactory->create()->load($itemId);

        if (!$item->getId()) {
            return false;
        }

        $wishList = $this->_wishlistFactory->create()->load($item->getWishlistId());

        if (!$wishList) {
            return false;
        } else {
            $data = $this->_trackingHelper->visitInfo();
            $data['item_id'] = $itemId;
            $data['product_id'] = $wishList->getProductId();
            $data['store_id'] = $wishList->getStoreId();
            $data['wishlist_id'] = $item->getWishlistId();
            $this->pushPages($data, self::REMOVE_WISHLIST);
        }
    }
}
