<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\ProductInterface;

/**
 * Defines the implementation class of the ProductInterface.
 */
class Product implements ProductInterface
{
    /**
     * @var \Targetbay\Tracking\Helper\Data
     */
    public $trackingHelper;

    /**
     * @param \Targetbay\Tracking\Helper\Data
     */
    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_trackingHelper = $trackingHelper;
    }

    /**
     * Get the total count of Products
     *
     * @return product count
     */
    public function getList()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        //$stockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $productFactory = $objectManager->create('\Magento\Catalog\Model\ProductFactory');

        $products = $productCollection->create()->addAttributeToSelect('*')->load()->toArray();

        foreach ($products as $id => $data) {
            $product = $productFactory->create()->load($id);
            $categoryIds = $product->getCategoryIds();
            $products[$id]['category_id'] = implode(',', $categoryIds);
            $products[$id]['category_name'] = $this->_trackingHelper->getProductCategoryName($product);
        }

        return $products;
    }
}
