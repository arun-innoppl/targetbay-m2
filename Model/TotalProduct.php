<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalProductInterface;

/**
 * Defines the implementation class of the TotalProduct.
 */
class TotalProduct implements TotalProductInterface
{

    /**
     * Get the Total Products
     *
     * @return totals
     */
    public function totalproductcount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productFactory = $objectManager->create('Magento\Catalog\Model\ProductFactory');
        $collection = $productFactory->create()->getCollection();
        $totals = [
            'total_products' => $collection->getSize()
        ];

        return json_encode($totals);
    }
}
