<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the get cart item interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface CartItemRepoInterface
{
    /**
     * get customer cart items.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Quote\Api\Data\CartSearchResultsInterface cart result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
