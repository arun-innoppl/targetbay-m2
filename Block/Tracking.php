<?php

namespace Targetbay\Tracking\Block;

class Tracking extends \Magento\Framework\View\Element\Template
{
    protected $trackingHelper;
    //protected $request;
    protected $registry;
    protected $customerSession;
    protected $cookieManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
       // \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        parent::__construct($context);
        $this->_isScopePrivate = true;
        $this->trackingHelper = $trackingHelper;
        $this->cookieManager = $cookieManager;
       // $this->request = $request;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
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
}
