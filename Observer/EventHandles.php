<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class EventHandles
 *
 * Custom handles to the page
 *
 * @package Targetbay\Tracking\Observer
 */
class EventHandles implements ObserverInterface
{
    const ANONYMOUS_USER = 'anonymous';
    const TIMEOUT = 900;

    private $apiToken;
    private $indexName;
    private $targetbayHost;
    public $request;

    /**
     * @var \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    public $trackingHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * @param \Targetbay\Tracking\Helper\Data $trackingHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_trackingHelper  = $trackingHelper;
        $this->_cookieManager = $cookieManager;
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName       = $this->_trackingHelper->getApiIndex();
        $expireAfter = 1380;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coreSession = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');

        if (empty($this->_cookieManager->getCookie('trackingsession'))) {
            $visitor_data = $coreSession->getVisitorData();
            $visitorId = $visitor_data['visitor_id'] . strtotime(date('Y-m-d H:i:s'));
            $trackingSession = $visitorId;
            $coreSession->setTrackingSessionId($trackingSession);
        }

        if (!empty($coreSession->getLastSession())) {
            $secondsInactive = time() - $coreSession->getLastSession();
            $expireAfterSeconds = $expireAfter * 60;
            if ($secondsInactive > $expireAfterSeconds) {
                $coreSession->unsProductReviewCount();
                $coreSession->unsProductReviewResponse();
                $coreSession->unsSiteReviewCount();
                $coreSession->unsSiteReviewResponse();
                $coreSession->unsQaReviewCount();
                $coreSession->unsQaReviewResponse();
                $coreSession->unsProductReviewId();
            }
        }
    }

    /**
     * Set the cookie values for user differentiate.
     */
    public function setCookieValues()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coreSession = $objectManager->get('Magento\Framework\Session\Generic');
        $_cookieMetadata = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');

        $customerName = self::ANONYMOUS_USER;
        $customerEmail = self::ANONYMOUS_USER;
        $customerId = '';

        $metadata = $_cookieMetadata
            ->createPublicCookieMetadata()
            ->setDuration(self::TIMEOUT)
            ->setPath('/')
            ->setDomain($coreSession->getCookieDomain())
            ->setHttpOnly(false);

        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
            $customerName = $customer->getName();
            $customerId = $customer->getId();
            $customerEmail = $customer->getEmail();
            $this->_cookieManager->setPublicCookie('user_loggedin', true, $metadata);
            $this->_cookieManager->setPublicCookie('afterlogin_session_id', 
                                                    $coreSession->getCustomerSessionId(), 
                                                    $metadata);
            $this->_cookieManager->setPublicCookie('trackingid', $customerId, $metadata);
        } else {
            if (!empty($this->_request->getParam('guest_user_id'))) {
                $customerId = $coreSession->getTrackingSessionId();
            } elseif (empty($this->_cookieManager->getCookie('trackingid'))) {
                $customerId = $coreSession->getTrackingSessionId();
            }
            $this->_cookieManager->setPublicCookie('user_loggedin', false, $metadata);
        }
        !empty($customerId) ? $this->_cookieManager->setPublicCookie('trackingid', 
                                                                      $customerId, 
                                                                      $metadata) : '';

        $this->_cookieManager->setPublicCookie('trackingemail', $customerEmail, $metadata);
        $this->_cookieManager->setPublicCookie('trackingname', $customerName, $metadata);

        $quoteId = $this->_checkoutSession->getQuoteId() ? $this->_checkoutSession->getQuoteId() : '';
        $this->_cookieManager->setPublicCookie('trackingorderid', $quoteId, $metadata);

        if (!$this->_cookieManager->getCookie('trackingsession')) {
            $this->_cookieManager->setPublicCookie('trackingsession', 
                                                    $coreSession->getTrackingSessionId(), 
                                                    $metadata);
        }
    }

    /**
     * Visiting page info
     *
     * @event controller_action_postdispatch
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        $coreSession = $objectManager->get('Magento\Framework\Session\Generic');
        $_cookieMetadata = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');

        $metadata = $_cookieMetadata
            ->createPublicCookieMetadata()
            ->setDuration(self::TIMEOUT)
            ->setPath('/')
            ->setDomain($coreSession->getCookieDomain())
            ->setHttpOnly(false);

        $this->setCookieValues();

        if (($requestInterface->getParam('utm_source') !== '') 
                && !$this->_cookieManager->getCookie('utm_source')) {
            $this->_cookieManager->setPublicCookie('utm_source', 
                                                    $requestInterface->getParam('utm_source'), 
                                                    $metadata);
        }

        if (($requestInterface->getParam('token') !== '') 
                && !$this->_cookieManager->getCookie('utm_token')) {
            $this->_cookieManager->setPublicCookie('utm_token', 
                                                    $requestInterface->getParam('token'), 
                                                    $metadata);
        }
    }
}