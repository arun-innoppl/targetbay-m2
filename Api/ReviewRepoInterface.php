<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * @api
 */
interface ReviewRepoInterface
{

    /**
     * Get review list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Review\Api\Data\ReviewSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
