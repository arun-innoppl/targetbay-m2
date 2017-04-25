<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * @api
 */
interface CategoryRepoInterface
{

    /**
     * Get catregory list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\CategorySearchResultInterface Category search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}
