<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateCartEventHandler implements ObserverInterface
{
    //const ANONYMOUS_USER = 'anonymous';
    //const ALL_PAGES = 'all';
    //const PAGE_VISIT = 'page-visit';
    //const PAGE_REFERRAL = 'referrer';
    const UPDATECART = 'update-cart';

    protected $_productRepository;
    protected $_cart;
    protected $_trackingHelper;
    protected $_checkoutSession;
    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Catalog\Model\ProductRepository $_productRepository,
        \Magento\Checkout\Model\Cart $_cart,
        \Magento\Checkout\Model\Session $_checkoutSession
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_productRepository = $_productRepository;
        $this->_cart = $_cart;
        $this->_checkoutSession = $_checkoutSession;
        $this->_apiToken = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName = $this->_trackingHelper->getApiIndex();
        $this->_tbHost = $this->_trackingHelper->getHostname();
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

    /**
     * Capture the Update cart event
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::UPDATECART)) {
            return false;
        }
        $items = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        $requestInfo = $observer->getEvent()->getInfo();
        $data = $this->_trackingHelper->getCartInfo();

        foreach ($items as $item) {
            $newQty = $requestInfo [$item->getId()] ['qty'];
            $oldQty = $item->getQty();
            if ($newQty == 0 || ($newQty == $oldQty)) {
                continue;
            }
            $itemData = $this->_trackingHelper->getItemInfo($item);
            unset($itemData ['quantity']);
            $itemData ['old_quantity'] = $oldQty;
            $itemData ['new_quantity'] = $newQty;
            $data ['cart_items'] [] = $itemData;
        }
        if (isset($data ['cart_items'])) {
            $this->pushPages($data, self::UPDATECART);
        }
    }
}
