<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class SearchProductEventHandler implements ObserverInterface
{
    //const ANONYMOUS_USER = 'anonymous';
    //const ALL_PAGES = 'all';
    //const PAGE_VISIT = 'page-visit';
    //const PAGE_REFERRAL = 'referrer';
    const CATALOG_SEARCH = 'searched';

    protected $_productRepository;
    protected $_trackingHelper;
    protected $_request;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Catalog\Model\ProductRepository $_productRepository,
        RequestInterface $_request
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_productRepository = $_productRepository;
        $this->_request = $_request;
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
