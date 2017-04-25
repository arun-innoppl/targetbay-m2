<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * @api
 */
interface SubscriberRepoInterface
{

    /**
     * Get customer subscribers list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\CustomerSearchResultInterface customer subscribers result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
