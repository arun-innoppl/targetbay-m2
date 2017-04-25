<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Defines the implementation class of the Review.
 */
class Tracking extends AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_coreSession;

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Session\Generic $coreSession
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
        \Magento\Framework\Session\Generic $_coreSession
    ) {
        $this->_cookieManager = $_cookieManager;
        $this->_coreSession = $_coreSession;
        //$sess = $this->_coreSession->getTrackingSessionId();
        if (!$this->_cookieManager->getCookie('trackingsession')) {
            $trackingSession = $this->_coreSession->getVisitorData()['visitor_id'];
            $this->_coreSession->setTrackingSessionId($trackingSession);
        }
    }
}
