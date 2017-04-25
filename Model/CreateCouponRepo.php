<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CreateCouponInterface;

/**
 * Defines the implementation class of the CreateCouponInterface.
 */
class CreateCouponRepo implements CreateCouponInterface
{

    /**
     * @var \Magento\Framework\App\RequestInterface $_request
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $_request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $_request
    ) {
        $this->_request = $_request;
    }

    /**
          * create coupon code.
     * @throws \Exception
     * @return int
          */
    public function createcoupon()
    {
        $times_used = $this->_request->getParam('no_times');
        $expiry_days = $this->_request->getParam('expiry_date');
        $expiry_hrs = $this->_request->getParam('expiry_hrs');
    }
}
