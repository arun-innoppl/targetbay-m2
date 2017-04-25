<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Defines the implementation class of the Review.
 */
class Review extends AbstractModel
{

    /**
     * @var \Targetbay\Tracking\Helper\Data
     */
    protected $_trackingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @param \Targetbay\Tracking\Helper\Data
     * @param \Magento\Customer\Model\Session
     * @param \Magento\Framework\App\RequestInterface
     * @param \Magento\Framework\HTTP\ZendClientFactory
     */
    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\App\RequestInterface $_request,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Framework\HTTP\ZendClientFactory $_httpClientFactory
    ) {
        $this->_trackingHelper = $_trackingHelper;
        $this->_request = $_request;
        $this->_customerSession = $_customerSession;
        $this->_httpClientFactory = $_httpClientFactory;
    }

    /**
     * Get the customer reviews
     *
     * @return string
     */
    public function getCustomerReviewCollection()
    {
        $_hostname = $this->_trackingHelper->getHostname();
        $_api_token = $this->_trackingHelper->getApiToken();
        $_api_index = $this->_trackingHelper->getApiIndex();
        $_params = [];
        $customerId = '';

        $_page_num = 0;
        if ($this->_request->getParam('p')) {
            $_page_num = $this->_request->getParam('p');
        }

        if ($this->_request->getParam('size')) {
            $_limit = $this->_request->getParam('size');
        } else {
            $_limit = $this->_trackingHelper->getReviewPageSize();
        }

        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
        }

        if (trim($_hostname) === '' || trim($_api_token) === '' || trim($_api_index) === '' || trim($customerId) === '') {
            return false;
        }

        $url = $_hostname . 'review-list-by-user/' . $customerId . '/' . $_page_num . '/' . $_limit . '?api_token=' . $_api_token;

        $_params['index_name'] = $_api_index;

        $jsonData = json_encode($_params);

        $client = $this->_httpClientFactory->create();
        $client->setUri((string) $url);
        $client->setConfig([
            'maxredirects' => 0,
            'timeout' => 1
        ]);
        $client->setRawData(utf8_encode($jsonData));
        $response = '';
        try {
            $response = $client->request(\Zend_Http_Client::POST)->getBody();
        } catch (\Exception $e) {
            $this->_trackingHelper->debug("ERROR:" . $e->getMessage());
        }
        return $response;
    }
}
