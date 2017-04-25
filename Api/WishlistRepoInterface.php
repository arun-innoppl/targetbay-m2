<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the get wishlist item interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface WishlistRepoInterface
{
    /**
     * get wishlist items.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Quote\Api\Data\CartSearchResultsInterface cart result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
