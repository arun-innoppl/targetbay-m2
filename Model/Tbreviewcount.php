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
     * @var \Magento\Framework\App\RequestInterface $request
     */
    public $request;

    /**
     * @var \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    public $trackingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public $storeManager;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_request = $request;
        $this->_trackingHelper = $trackingHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get targetbay review count
     *
     * @return boolean
     */
    public function tbreviewcount()
    {
        $pageReference = $this->_request->getParam('page_identifier') ? $this->_request->getParam('page_identifier') : '';

        $reviewCount = $this->_request->getParam('review_count') ? $this->_request->getParam('review_count') : '';

        $productId = $this->_request->getParam('product_id') ? $this->_request->getParam('product_id') : '';

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coreSession = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');

        if (!empty($pageReference) && $reviewCount > 0) {
            try {
                if ($pageReference == Targetbay_Tracking_Helper_Data::RATINGS_STATS) {
                    $coreSession->setProductReviewCount($reviewCount);
                } elseif ($pageReference == Targetbay_Tracking_Helper_Data::QUESTION_STATS) {
                    $coreSession->setQaReviewCount($reviewCount);
                } elseif ($pageReference == Targetbay_Tracking_Helper_Data::RATINGS_STATS
                    && $productId == ''
                ) {
                    $coreSession->setSiteReviewCount($reviewCount);
                }
            } catch (\Exception $e) {
                $this->_trackingHelper->debug('Error :' . $e);
            }
            return true;
        } else {
            return false;
        }
    }
}
