<?php

namespace Targetbay\Tracking\Controller\Index;

use Magento\Framework\App\Action\Context as Context;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Customer\Model\Session as CustomerSession;

class Reload extends \Magento\Framework\App\Action\Action
{
    const TIMEOUT = 900;

    public $customerSession;
    public $cart;
    public $coreSession;
    public $productFactory;

    /**
     * @param Magento\Framework\App\Action\Context $context
     * @param CustomerSession $customerSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(Context $context,
        CustomerSession $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $productFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->context=$context;
        $this->customerSession = $customerSession;
        $this->coreSession = $coreSession;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    /**
     * Reload product to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $cookieMetadata = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
        $cookieManager = $objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $utmSource = $this->getRequest()->getParam('utm_source');
        $utmToken = $this->getRequest()->getParam('token');
        $quoteId = (int)$this->getRequest()->getParam('quote_id');
        $guestUserId = $this->getRequest()->getParam('guest_user_id');

        $resultRedirect->setPath('checkout/cart', ['utm_source' => $utmSource, 'token' => $utmToken]);

        $metadata = $cookieMetadata
                    ->createPublicCookieMetadata()
                    ->setDuration(self::TIMEOUT)
                    ->setPath('/')
                    ->setDomain($this->coreSession->getCookieDomain())
                    ->setHttpOnly(false);

        if ($guestUserId != '' && !$this->customerSession->isLoggedIn()) {
            $cookieManager->setPublicCookie('targetbay_session_id', $guestUserId, $metadata);
        }

        if ($this->customerSession->isLoggedIn()) {
            return $resultRedirect;
        }

        if (empty($quoteId)) {
            return $resultRedirect;
        }

        try {
            $this->coreSession->setRestoreQuoteId($quoteId);
            $this->coreSession->setAbandonedMail(true);
            $quote = $objectManager->get('Magento\Quote\Model\Quote')->load($quoteId);

            $quoteItems = $quote->getAllVisibleItems();
            $i=0;
            foreach ($quoteItems as $key => $item) {
                $product = $this->productFactory->load($item->getProductId());
                if ($item->getProductType() == 'configurable') {
                    $customOptions = $item->getProduct()->getTypeInstance(true)
                                            ->getOrderOptions($item->getProduct());
                    $superAttributeInfo = $customOptions['info_buyRequest'];

                    $params = array('qty' => $quoteItems[$i]['qty'], 
                                    'super_attribute' => $superAttributeInfo['super_attribute']
                                    );
                    $this->cart->addProduct($product, $params);
                } else {
                    $params = array('qty' => $quoteItems[$i]['qty']);
                    $this->cart->addProduct($product, $params);
                }
                $i++;
            }
            $this->cart->save();
        } catch (\Exception $e) {
            $this->logger->addDebug('Failed to add product into cart. Error: '.$e);
        }
        return $resultRedirect;
    }
}
