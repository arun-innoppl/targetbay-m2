<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * @api
 */
interface OrderRepoInterface
{

    /**
     * Get order list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface Order search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
