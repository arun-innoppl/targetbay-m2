<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalCategoryInterface;

/**
 * Defines the implementation class of the TotalCategory.
 */
class TotalCategory implements TotalCategoryInterface
{

    /**
     * Get the Total Category
     *
     * @return totals
     */
    public function totalcategorycount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $categoryFactory = $objectManager->create('Magento\Catalog\Model\CategoryFactory');

        $collection = $categoryFactory->create()->getCollection();

        $totals = [
            'total_categories' => $collection->getSize()
        ];

        return json_encode($totals);
    }
}
