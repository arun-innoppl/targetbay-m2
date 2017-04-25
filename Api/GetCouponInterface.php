<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * @api
 */
interface GetCouponInterface
{

    /**
     * Get coupon list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\SalesRule\Api\Data\CouponSearchResultInterface coupon result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
