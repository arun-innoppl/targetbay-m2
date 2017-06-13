<?php
/*
 * @author TargetBay
 * @copyright - Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Helper;

/**
 * Class Data
 *
 * Custom helper class
 *
 * @package Targetbay\Tracking\Helper
 */
class Inventary extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $registry;
    protected $trackingInventaryHelper;
    protected $storeManager;
    protected $productModel;

    public function __construct(
            \Magento\Framework\Registry $registry,
            \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Catalog\Model\Product $productModel
    ) {
        $this->registry = $registry;
        $this->stockItemRepository = $stockItemRepository;
        $this->storeManager = $storeManager;
        $this->productModel = $productModel;
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getStockItem($productId)
    {
        return $this->stockItemRepository->get($productId);
    }

    public function getProductInfo()
    {
        $product = $this->getProduct();
        $currentProductId = $product->getId();
        $currentProductTypeId = $product->getTypeId();
        $storeId = $this->storeManager->getStore()->getId();
        $data =  array();
        $childProductIds = array();

        switch ($currentProductTypeId) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($storeId, $product);
                $childProducts = $productTypeInstance->getUsedProducts($product);
                $i=0;
                foreach($childProducts as $child)
                {
                    $productStock = $this->getStockItem($child->getId());
                    if($productStock->getIsInStock() == 0)
                    {
                        $childProductIds[] = $child->getId();
                    }
                }
                foreach($childProductIds as $key => $value)
                {
                    $data[$i]['child_product_id'] = $value;
                    $options = $this->getConfigurableProductOption($currentProductId, $value);
                    $data[$i]['options'] = $options;
                    $i++;
                }
                break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($storeId, $product);
                $childProducts = $productTypeInstance->getAssociatedProducts($product);
                foreach($childProducts as $child)
                {
                    $productStock = $this->getStockItem($child->getId());
                    if($productStock->getIsInStock() == 0)
                    {
                        $childProductIds[] = $child->getId();
                    }
                }
                $i=0;
                foreach($childProductIds as $key => $value)
                {
                    $data[$i]['child_product_id'] = $value;
                    $childProductName = $this->getProductNameOption($value);
                    $data[$i]['options'] = $childProductName;
                    $i++;
                }
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($storeId, $product);
                $selectionCollection = $productTypeInstance
                                ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);  
                
                $i=0;
                foreach($selectionCollection as $child) 
                {
                    $productStock = $this->getStockItem($child->getId());
                    if($productStock->getIsInStock() == 0)
                    {                       
                        $childProductIds[] = $child->getId();
                    }
                }
                foreach($childProductIds as $key => $value)
                {
                    $data[$i]['child_product_id'] = $value;
                    $childProductName = $this->getProductNameOption($value);
                    $data[$i]['options'] = $childProductName;
                    $i++;
                }         
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
                $data['child_product_id'] = '';
                $data['options'] = '';
                break;
            default:
                break;
        }
        return json_encode($data);
    }

    public function getProductNameOption($childProductId) {
        $productData =  array();
        $quotes = array("'", '"');
        $replace = array('', '');
        $storeId = $this->storeManager->getStore()->getId();
        $childProduct = $this->productModel->setStoreId($storeId)->load($childProductId);
        $productName = $childProduct->getName();
        $productData['name'] = str_replace($quotes, $replace, $productName);
        return $productData;
    }

    public function getConfigurableProductOption($parentProductId, $childProductId) {
        $storeId = $this->storeManager->getStore()->getId();
        $parentProduct = $this->productModel->setStoreId($storeId)->load($parentProductId);
        $attributes = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct); 
        $childProduct = $this->productModel->load($childProductId);
        $optionLabel = array();
        foreach ($attributes as $key => $attribute){
            foreach ($attribute['values'] as $value){
                $childValue = $childProduct->getData($attribute['attribute_code']);
                if($value['value_index'] == $childValue){
                    $optionLabel[] = $attribute['store_label'].':'.$value['store_label'];
                }
            }
        }
        return $optionLabel;
    }

    public function getConfigurableProductOptionIds($parentProductId, $childProductId) {
        $storeId = $this->storeManager->getStore()->getId();
        $parentProduct = $this->productModel->setStoreId($storeId)
                                ->load($parentProductId);
        $attributes = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct); 
        $childProduct = $this->productModel->setStoreId($storeId)
                                ->load($childProductId);

        $optionLabel = array();
        $optionValue = array();
        foreach ($attributes as $key => $attribute){
            foreach ($attribute['values'] as $value){
                $childValue = $childProduct->getData($attribute['attribute_code']);
                if($value['value_index'] == $childValue) {
                    $attributeId = $attribute['attribute_id'];
                    $optionValue[$attributeId] = $value['value_index'];
                }
            }
        }
        return $optionValue;
    }

    public function getBundledProductOptionIds($parentProductId, $childProductId) {
        $storeId = $this->storeManager->getStore()->getId();
        $bundledProduct = $this->productModel->setStoreId($storeId)
                                ->load($parentProductId);
        $selectionCollection = $bundledProduct->getTypeInstance(true)->getSelectionsCollection(
            $bundledProduct->getTypeInstance(true)->getOptionsIds($bundledProduct), $bundledProduct
        ); 
        $bundledItems = array();
        foreach($selectionCollection as $option) 
        {
            $entityId = $option->getEntityId();
            if(in_array($entityId, $childProductId)) {
                $bundleOptionId = $option->getOptionId();
                $bundledItems[$bundleOptionId] = $option->getSelectionId();  
            }
        }
        return $bundledItems;
    }

    public function getInventryStatus()
    {
        $product = $this->getProduct();
        $currentProductId = $product->getId();
        $currentProductTypeId = $product->getTypeId();
        $storeId = $this->storeManager->getStore()->getId();
        $status = 0;
        $stockstatus = array();
        switch ($currentProductTypeId) {
            case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($storeId, $product);
                $childProducts = $productTypeInstance->getUsedProducts($product);
                foreach($childProducts as $child)
                {
                    $productStock = $this->getStockItem($child->getId());
                    if($productStock->getIsInStock() == 0)
                    {
                        $stockstatus[] = $child->getId();
                    }
                }
                if(count($stockstatus) > 0) {
                    $status = true;
                }
            break;
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $productTypeInstance = $product->getTypeInstance();
                $productTypeInstance->setStoreFilter($storeId, $product);
                $childProducts = $productTypeInstance->getAssociatedProducts($product);
                foreach($childProducts as $child)
                {                  
                    $productStock = $this->getStockItem($child->getId());
                    if($productStock->getIsInStock() == 0)
                    {
                        $stockstatus[] = $child->getId();
                    }
                }
                if(count($stockstatus) > 0) {
                    $status = true;
                }
            break;
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:

                $productTypeInstance = $product->getTypeInstance(true);
                $productTypeInstance->setStoreFilter($storeId, $product);
                $selectionCollection = $productTypeInstance->getSelectionsCollection(
                                                $product->getTypeInstance(true)->getOptionsIds($product),
                                                $product
                                            );
                
                $i=0;
                foreach($selectionCollection as $child) 
                {
                    $productStock = $this->getStockItem($child->getId());
                    if($productStock->getIsInStock() == 0)
                    {
                        $stockstatus[] = $child->getId();
                    }
                }
                if(count($stockstatus) > 0) {
                    $status = true;
                }         
            break;
            case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:

                $productStock = $this->getStockItem($currentProductId);
                if($productStock->getIsInStock() == 0)
                {
                    $status =  true;
                }
            break;
            default:
            break;
        }
        return $status;
    }
}
