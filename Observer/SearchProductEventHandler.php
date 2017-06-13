<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class SearchProductEventHandler implements ObserverInterface
{
    const CATALOG_SEARCH = 'searched';

    public $productRepository;
    public $trackingHelper;
    public $request;
    private $apiToken;
    private $indexName;
    private $tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        RequestInterface $request
    ) {
        $this->_trackingHelper = $trackingHelper;
        $this->_productRepository = $productRepository;
        $this->_request = $request;
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
            $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
        } catch (\Exception $e) {
            $this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
        }
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_trackingHelper->canTrackPages(self::CATALOG_SEARCH)) {
            return false;
        }
        $keyword = $this->_request->getParam('q');
        if (empty($keyword)) {
            return false;
        }
        $data = $this->_trackingHelper->visitInfo();
        $data ['keyword'] = $keyword;
        $this->pushPages($data, self::CATALOG_SEARCH);
    }
}
