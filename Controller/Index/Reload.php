<?php

namespace Targetbay\Tracking\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Reload extends \Magento\Framework\App\Action\Action
{
    const TIMEOUT = 900;
    private $customerSession;
    private $cart;
    private $coreSession;
    private $context;

    /**
     * @param Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct($context);
        $this->context=$context;
        $this->customerSession = $customerSession;
        $this->coreSession = $coreSession;
        $this->cart = $cart;
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

            foreach ($quoteItems as $key => $item) {
                $itemData['itemQty'] = $item->getQty();
                $itemData['itemProductId'] = $item->getProductId();
                $itemQty[$item->getProductId()] = $item->getQty();
                $itemIds[$item->getProductId()] = $item->getId();
                $prodId[] = $item->getProductId();
                $this->cart->addProduct($item->getProductId(), ['qty' => $item->getQty()]);
            }
            $this->cart->save();
        } catch (\Exception $e) {
            $objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        return $resultRedirect;
    }
}
