<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\ListProductInterface;

/**
 * Defines the implementation class of the ListProductInterface.
 */
class ListProducts implements ListProductInterface
{
    // Product type configurable.
    const CONFIGURABLE_PRODUCT = 'configurable';
    const BUNDLE_PRODUCT = 'bundle';

    /**
     * Get the Products with pagination
     *
     * @return products
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $stockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');

        $productFactory = $objectManager->create('\Magento\Catalog\Model\ProductFactory');
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        //$imageHelper = $objectManager->get('\Magento\Catalog\Helper\Image');
        $trackingHelper = $objectManager->get('\Targetbay\Tracking\Helper\Data');
        $collection = $productCollection->create()->addAttributeToSelect('*');

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $products = $collection->load()->toArray();

        foreach ($products as $id => $data) {
            $product = $productFactory->create()->load($id);
            $categoryIds = $product->getCategoryIds();

            if (!empty($product->getImage()) && $product->getImage() !== 'no_selection') {
                $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
            } else {
                $imageUrl = '';
            }

            if(!empty($product->getUrlKey())) {
                $urlKey = $product->getUrlKey();
            } else {
                $urlKey = $product->getProductUrl();
            }

            $products[$id]['image_url'] = $imageUrl;
            $products[$id]['category_id'] = implode(',', $categoryIds);
            $products[$id]['stock_count'] = $stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
            $products[$id]['abstract'] = $product->getAbstract();
            $products[$id]['currency'] = $store->getCurrentCurrencyCode();
            $products[$id]['visibility'] = $product->getVisibility();
            $products[$id]['status'] = $product->getStatus();
            $products[$id]['url_key'] = $urlKey;
            $products[$id]['website_id'] = $product->getWebsiteIds();
            $products[$id]['store_id'] = $product->getStoreIds();
            $products[$id]['price'] = $product->getFinalPrice();
            $products[$id]['special_price'] = $product->getSpecialPrice();

            /**
             *
             * @var $category Get product categories
             */
            $products[$id]['related_product_id'] = implode(',', $product->getRelatedProductIds());
            $products[$id]['upsell_product_id'] = implode(',', $product->getUpSellProductIds());
            $products[$id]['crosssell_product_id'] = implode(',', $product->getCrossSellProducts());

            $configOptions = [];
            $customOptions = [];
            $childProductData = [];

            switch ($product->getTypeId()) {
                case self::CONFIGURABLE_PRODUCT:
                    if ($productAttributeOptions = $productFactory->create()->load($product->getId())->getTypeInstance(true)->getConfigurableAttributesAsArray($product)) {
                        $configOptions = $trackingHelper->productOptions($productAttributeOptions, 'label');
                    }

                    $childProducts = $product->getTypeInstance()->getUsedProductIds($product);
                    foreach ($childProducts as $childProductId) {
                        $childProductDetails = $productFactory->create()->load($childProductId);
                        $childProductData[$childProductId] = $trackingHelper->getProductData($childProductDetails);
                        $childProductData[$childProductId]['parent_id'] = $product->getId();
                    }
                    $products[$id]['child_items'] = $childProductData;
                    $products[$id]['parent_id'] = $product->getId();
                    break;
                case self::BUNDLE_PRODUCT:
                    $collection = $product->getTypeInstance(true)
                        ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);

                    foreach ($collection as $item) {
                        $childProductId = $item->getId();
                        $childProductDetails = $productFactory->create()->load($item->getId());
                        $childProductData[$childProductId] = $trackingHelper->getProductData($childProductDetails);
                        $childProductData[$childProductId]['parent_id'] = $product->getId();
                    }
                    $products[$id]['child_items'] = $childProductData;
                    $products[$id]['parent_id'] = $product->getId();
                    break;
            }

            if ($custOptions = $productFactory->create()->load($product->getId())->getOptions()) {
                $customOptions = $trackingHelper->productOptions($custOptions);
            }
            $options = array_merge($configOptions, $customOptions);

            if (!empty($options)) {
                $products[$id]['attributes'] = $options;
            }
        }

        return $products;
    }
}
