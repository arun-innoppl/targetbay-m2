<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class AddProductEventHandler implements ObserverInterface
{
    const ADD_PRODUCT = 'add-product';
    const UPDATE_PRODUCT = 'update-product';

    public $request;
    public $trackingHelper;
    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        RequestInterface $request,
        \Targetbay\Tracking\Helper\Data $trackingHelper
    ) {
        $this->_request = $request;
        $this->_trackingHelper  = $trackingHelper;
        $this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
        $this->_indexName       = $this->_trackingHelper->getApiIndex();
        $this->_tbHost   = $this->_trackingHelper->getHostname();
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
            $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::ADD_PRODUCT)) {
            return false;
        }

        $product = $observer->getEvent()->getProduct();

        if ($product->getId()) {
            $type = self::ADD_PRODUCT;
            if ($this->_request->getParam('id')) {
                if (!$this->_trackingHelper->canTrackPages(self::UPDATE_PRODUCT)) {
                    return false;
                }
                $type = self::UPDATE_PRODUCT;
            }
            $data = $this->_trackingHelper->getProductData($product);
            $this->pushPages($data, $type);
        }
    }
}
