<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * @api
 */
interface UpdateCouponInterface
{
    /**
     * update coupon code.
     * @throws \Exception
     * @return int
     */
    public function updatecoupon();
}
