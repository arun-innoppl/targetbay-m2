<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\UpdateCouponInterface;

/**
 * Defines the implementation class of the UpdateCouponInterface.
 */
class UpdateCouponRepo implements UpdateCouponInterface
{
    /**
     * update coupon code.
     * @throws \Exception
     * @return int
     */
    public function updatecoupon()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryCollection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        //$categoryFactory = $objectManager->create('\Magento\Catalog\Model\CategoryFactory');

        $collection = $categoryCollection->create()->addAttributeToSelect('*');

        // ToDo: Fix $searchCriteria is undefined.
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $category = $collection->load()->toArray();

        return $category;
    }
}
