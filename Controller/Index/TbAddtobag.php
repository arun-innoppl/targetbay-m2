<?php

namespace Targetbay\Tracking\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class TbAddtobag extends \Magento\Framework\App\Action\Action
{
    private $context;
    private $cart;
    private $productFactory;
    protected $trackingInventaryHelper;
    protected $storeManager;

    /**
     * @param Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $productFactory
     * @param \Targetbay\Tracking\Helper\Inventary $trackingInventaryHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $productFactory,
        \Targetbay\Tracking\Helper\Inventary $trackingInventaryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->context=$context;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->trackingInventaryHelper = $trackingInventaryHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Reload product to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $utmSource = $this->getRequest()->getParam('utm_source');
        $utmToken = $this->getRequest()->getParam('token');
        $productData = base64_decode($this->getRequest()->getParam('product_id'));

        $staticArrayDecode = json_decode($productData, true);

        $productIds = $staticArrayDecode;
        $storeId = $this->storeManager->getStore()->getId();
        $qty = 1;

        $resultRedirect->setPath('checkout/cart', ['utm_source' => $utmSource, 'token' => $utmToken]);

        //$resultRedirect->setPath('checkout/cart');

        if (count($staticArrayDecode) < 1) {
            return $resultRedirect;
        }

        try {
            $bundleOptionQty = array();

            foreach ($staticArrayDecode as $key => $value) {
                $productId = $key;
                $productIds = $value;

                if (count($productIds) < 1) { 
                    return false; 
                }

                foreach ($productIds as $key => $product_id) {
                    $childProductId = $product_id;
                    $product = $this->productFactory->setStoreId($storeId)->load($productId);
                    $productType = $product->getTypeId();
                    switch ($productType) {
                        case \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE:
                            $productOptions = $this->trackingInventaryHelper->getConfigurableProductOptionIds($productId, $childProductId);

                            $productDetail = $this->productFactory->setStoreId($storeId)->load($productId);

                            $params = array('product' => $productId, 
                                            'selected_configurable_option' =>  $childProductId, 
                                            'super_attribute' =>  $productOptions, 
                                            'qty' => $qty);

                        break;
                        case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                            $option = array($childProductId => $qty);
                            $params = array('super_group' =>  $option);

                            $productDetail = $this->productFactory->setStoreId($storeId)->load($productId);
                        break;                  
                        default:                         
                            $productDetail = $this->productFactory->setStoreId($storeId)->load($productId);
                            $params = array('qty' => $qty);   
                        break;
                    }
                    $this->cart->addProduct($productDetail, $params);
                }
                if($productType == 'bundle') {
                    $bundleOption = $this->trackingInventaryHelper->getBundledProductOptionIds($productId, $value);

                    foreach ($bundleOption as $key => $value) {
                        $bundleOptionQty[$key] = $qty;
                    }

                    $productDetail = $this->productFactory->setStoreId($storeId)->load($productId);

                    $params = array('bundle_option' => $bundleOption,
                                    'qty' => $qty,
                                    'product' => $productId);
                    $this->cart->addProduct($productDetail, $params);
                }
            }
            $this->cart->save();
        } catch (\Exception $e) {
            $objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        return $resultRedirect;
    }
}