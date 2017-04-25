<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CategoryRepoInterface;

/**
 * Defines the implementation class of the CategoryRepoInterface.
 */
class CategoryRepo implements CategoryRepoInterface
{
    /**
     * Get the list of categories
     *
     * @return CategoryRepoInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryCollection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');

        $collection = $categoryCollection->create()->addAttributeToSelect('*');

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $category = $collection->load()->toArray();

        return $category;
    }
}
