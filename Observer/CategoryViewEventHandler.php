<?php

namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;

class CategoryViewEventHandler implements ObserverInterface
{
    const CATEGORY_VIEW = 'category-view';

    protected $_trackingHelper;
    protected $_registry;

    private $_apiToken;
    private $_indexName;
    private $_tbHost;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\Registry $_registry
    ) {
        $this->_trackingHelper  = $_trackingHelper;
        $this->_registry = $_registry;
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
        if (!$this->_trackingHelper->canTrackPages(self::CATEGORY_VIEW)) {
            return false;
        }
        $category = $this->_registry->registry('current_category', true);
        $data = $this->_trackingHelper->visitInfo();
        $data ['category_id'] = $category->getId();
        $data ['category_url'] = $category->getUrl();
        $data ['category_name'] = $category->getName();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        $controllerName = $requestInterface->getControllerName();
        if ($controllerName === 'category') {
            return false;
        }

        $this->pushPages($data, self::CATEGORY_VIEW);
    }
}
