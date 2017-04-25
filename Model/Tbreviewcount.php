<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TbreviewcountInterface;

/**
 * Defines the implementation class of the Tbreviewcount.
 */
class Tbreviewcount implements TbreviewcountInterface
{

    /**
     * @var \Magento\Framework\App\RequestInterface $_request
     */
    protected $_request;

    /**
     * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
     */
    protected $_trackingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\RequestInterface $_request,
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager
    ) {
        $this->_request = $_request;
        $this->_trackingHelper = $_trackingHelper;
        $this->_storeManager = $_storeManager;
    }

    /**
     * Get targetbay review count
     *
     * @return boolean
     */
    public function tbreviewcount()
    {
        $pageReference = $this->_request->getParam('page_identifier') ? $this->_request->getParam('page_identifier') : '';

        $reviewCount = $this->_request->getParam('review_count') ? Mage::app()->getRequest()->getParam('review_count') : '';

        $productId = $this->_request->getParam('product_id') ? $this->_request->getParam('product_id') : '';

        if (!empty($pageReference) && $reviewCount > 0) {
            try {
                if ($pageReference == Targetbay_Tracking_Helper_Data::RATINGS_STATS) {
                    Mage::getSingleton('core/session')->setProductReviewCount($reviewCount);
                } elseif ($pageReference == Targetbay_Tracking_Helper_Data::QUESTION_STATS) {
                    Mage::getSingleton('core/session')->setQaReviewCount($reviewCount);
                } elseif ($pageReference == Targetbay_Tracking_Helper_Data::RATINGS_STATS
                    && $productId == ''
                ) {
                    Mage::getSingleton('core/session')->setSiteReviewCount($reviewCount);
                }
            } catch (\Exception $e) {
                Mage::helper('tracking')->debug($e->getMessage());
            }
            return true;
        } else {
            return false;
        }
    }
}
