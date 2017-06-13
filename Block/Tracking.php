<?php

namespace Targetbay\Tracking\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

class Tracking extends \Magento\Framework\View\Element\Template
{
    public $trackingHelper;
    public $trackingInventaryHelper;
    public $registry;
    public $customerSession;
    public $cookieManager;
    public $checkoutSession;
    public $stockItemRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Targetbay\Tracking\Helper\Inventary $trackingInventaryHelper,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
    ) {
        parent::__construct($context);
        $this->_isScopePrivate = true;
        $this->trackingHelper = $trackingHelper;
        $this->trackingInventaryHelper = $trackingInventaryHelper;
        $this->cookieManager = $cookieManager;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        return 0;
    }

    public function getProductInfo()
    {
        $moduleName = $this->getRequest()->getModuleName();
        $controllerName = $this->getRequest()->getControllerName();
        $productInfo = [];

        if ($moduleName === 'catalog' && $controllerName === 'product') {
            $product = $this->registry->registry('current_product');
            $productInfo['product_name'] = $product->getName();
            $productInfo['product_id'] = $product->getId();
            $productInfo['product_image'] = $this->trackingHelper->getImageUrl($product, 'image');
            $productInfo['product_url'] = $product->getUrlModel()->getUrl($product);
        }

        return $productInfo;
    }

    public function getUserInfo()
    {
        $userInfo = [];
        $visitorName = \Targetbay\Tracking\Helper\Data::ANONYMOUS_USER;
        if ($this->customerSession->isLoggedIn()) {
            $userInfo['user_id'] = $this->customerSession->getId();
            $userInfo['user_email'] = $this->customerSession->getCustomer()->getEmail();
            $userInfo['user_name'] = $this->customerSession->getCustomer()->getName();
        } else {
            $userInfo['user_id'] = $this->cookieManager->getCookie('targetbay_session_id');
            $userInfo['user_email'] = '';
            $userInfo['user_name'] = $visitorName;
        }

        return $userInfo;
    }

    public function getOrderId()
    {   
        $htmlTag = '';        
        $moduleName = $this->getRequest()->getModuleName();
        $actionName = $this->getRequest()->getActionName();
        if ($moduleName == 'checkout' && $actionName == 'success') {
            $lastOrderId = $this->checkoutSession->getLastOrderId();
            $htmlTag = '<div id="targetbay_order_reviews"></div>';
        }
        return $htmlTag;
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getProductStockInfo()
    {
        $_product = $this->getProduct();
        $controllername = $this->getRequest()->getControllerName();
        $modulename = $this->getRequest()->getModuleName();
        if($modulename == 'catalog' && $controllername == 'product') {
            if($this->trackingInventaryHelper->getInventryStatus() == 1) {
                return $this->trackingInventaryHelper->getProductInfo();
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function getStockAvaliability()
    {
        $_product = $this->getProduct();
        $controllername = $this->getRequest()->getControllerName();
        $modulename = $this->getRequest()->getModuleName();
        $backorderStatus = $this->trackingHelper->getBackorderStatus();
        if($modulename == 'catalog' && $controllername == 'product' && $backorderStatus == 1) {
            return $this->trackingInventaryHelper->getInventryStatus();
        } else {
            return 0;
        }
    }

    public function getStockItem($productId)
    {
        return $this->stockItemRepository->get($productId);
    }
}
