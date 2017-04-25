<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

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

    private $_apiToken;
    private $_indexName;
    private $_targetbayHost;
    protected $_request;

    /**
     * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
     */
    protected $_trackingHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Targetbay\Tracking\Helper\Data $_trackingHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager
     * @param \Magento\Customer\Model\Session $_customerSession
     * @param \Magento\Framework\App\RequestInterface $_request
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     */
    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Framework\App\RequestInterface $_request,
        \Magento\Checkout\Model\Session $_checkoutSession
    ) {
        $this->_trackingHelper  = $_trackingHelper;
        $this->_cookieManager = $_cookieManager;
        $this->_customerSession = $_customerSession;
        $this->_request = $_request;
        $this->_checkoutSession = $_checkoutSession;
        $this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName       = $this->_trackingHelper->getApiIndex();
        $expireAfter = 1380;
        //$this->_tbHost = $this->_trackingHelper->getHostname();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coreSession = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');

        if (empty($this->_cookieManager->getCookie('trackingsession'))) {
            $visitor_data = $coreSession->getVisitorData();
            $visitorId = $visitor_data['visitor_id'] . strtotime(date('Y-m-d H:i:s'));
            $trackingSession = $visitorId;
            $coreSession->setTrackingSessionId($trackingSession);
        }

        if (isset($_SESSION['last_session'])) {
            $secondsInactive = time() - $_SESSION['last_session'];
            $expireAfterSeconds = $expireAfter * 60;
            if ($secondsInactive > $expireAfterSeconds) {
                $coreSession->unsProductReviewCount();
                $coreSession->unsProductReviewResponse();
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

        // For anonymous user
        $customerName = self::ANONYMOUS_USER;
        $customerEmail = self::ANONYMOUS_USER;
        $customerId = '';

        $metadata = $_cookieMetadata
            ->createPublicCookieMetadata()
            ->setDuration(self::TIMEOUT)
            ->setPath('/')
            ->setDomain($coreSession->getCookieDomain())
            ->setHttpOnly(false);

        // for logged user.
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
            $customerName = $customer->getName();
            $customerId = $customer->getId();
            $customerEmail = $customer->getEmail();
            $this->_cookieManager->setPublicCookie('user_loggedin', true, $metadata);
            $this->_cookieManager->setPublicCookie('afterlogin_session_id', $coreSession->getCustomerSessionId(), $metadata);
	    $this->_cookieManager->setPublicCookie('trackingid', $customerId, $metadata);
        } else {
            if (!empty($this->_request->getParam('guest_user_id'))) {
                $customerId = $coreSession->getTrackingSessionId();
            } elseif (empty($this->_cookieManager->getCookie('trackingid'))) {
                $customerId = $coreSession->getTrackingSessionId();
            }
            empty($customerId) ? '' : $this->_cookieManager->setPublicCookie('trackingid', $customerId, $metadata);
            $this->_cookieManager->setPublicCookie('user_loggedin', false, $metadata);
        }

        //empty($customerId) ? '' : $this->_cookieManager->setPublicCookie('trackingid', $customerId, $metadata);

        $this->_cookieManager->setPublicCookie('trackingemail', $customerEmail, $metadata);
        $this->_cookieManager->setPublicCookie('trackingname', $customerName, $metadata);

        $quoteId = $this->_checkoutSession->getQuoteId() ? $this->_checkoutSession->getQuoteId() : '';
        $this->_cookieManager->setPublicCookie('trackingorderid', $quoteId, $metadata);

        if (!$this->_cookieManager->getCookie('trackingsession')) {
            $this->_cookieManager->setPublicCookie('trackingsession', $coreSession->getTrackingSessionId(), $metadata);
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

        // Set Token Values.
        if (($requestInterface->getParam('utm_source') !== '') && !$this->_cookieManager->getCookie('utm_source')) {
            $this->_cookieManager->setPublicCookie('utm_source', $requestInterface->getParam('utm_source'), $metadata);
        }

        if (($requestInterface->getParam('token') !== '') && !$this->_cookieManager->getCookie('utm_token')) {
            $this->_cookieManager->setPublicCookie('utm_token', $requestInterface->getParam('token'), $metadata);
        }
    }
}
