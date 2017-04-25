<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductViewEventHandler implements ObserverInterface
{
    const ANONYMOUS_USER = 'anonymous';
    const ALL_PAGES = 'all';
    const PAGE_VISIT = 'page-visit';
    const PAGE_REFERRAL = 'referrer';
    const PRODUCT_VIEW = 'product-view';

    // product stock status
    const IN_STOCK = 'in-stock';
    const OUT_OF_STOCK = 'out-stock';

    protected $_productRepository;
    protected $_trackingHelper;
    protected $_registry;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Catalog\Model\ProductRepository $_productRepository,
        \Magento\Framework\Registry $_registry
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_productRepository = $_productRepository;
        $this->_registry = $_registry;
        $this->_apiToken = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName = $this->_trackingHelper->getApiIndex();
        $this->_tbHost = $this->_trackingHelper->getHostname();
    }

    /**
     * API Calls
     *
     * @param $data
     * @param $type
     */
    public function pushPages($data, $type)
    {
        $endPointUrl = $this->_tbHost . $type . $this->_apiToken;
        $data ['index_name'] = $this->_indexName;
        try {
            $res = $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::PRODUCT_VIEW)) {
            return false;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        // Get the base visit info
        $data = $this->_trackingHelper->visitInfo();
        $product = $this->_registry->registry('product');
        $data ['category'] = $this->_trackingHelper->getProductCategory($product);
        $data ['product_id'] = $product->getId();
        $data ['product_name'] = $product->getName();
        $data ['msrp_price'] = $priceHelper->currency($product->getMsrp(), true, false);
        $data ['price'] = $product->getPrice();
        $data ['productimg'] = $this->_trackingHelper->getImageUrl($product, 'image');
        $data ['stock'] = self::OUT_OF_STOCK;
        //$stock = $product->getStockItem();
        if ($product->isAvailable()) {
            $data['stock'] = self::IN_STOCK;
        }

        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        $controllerName = $requestInterface->getControllerName();
        if ($controllerName == 'product') {
            return false;
        }

        $this->pushPages($data, self::PRODUCT_VIEW);
    }
}
