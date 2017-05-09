<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\UpdateRewardPointsInterface;

/**
 * Defines the implementaiton class of the UpdateRewardPoints.
 */
class UpdateRewardPoints implements UpdateRewardPointsInterface
{
    const REWARD_ACTION_REVIEW = 6;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Targetbay\Tracking\Helper\Data
     */
    protected $_trackingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Reward helper
     *
     * @var \Magento\Reward\Helper\Data
     */
    //protected $_rewardData;

    public function __construct(
        \Magento\Framework\App\RequestInterface $_request,
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager
        //Magento\Reward\Helper\Data $_rewardData
    ) {
        $this->_request = $_request;
        $this->_trackingHelper = $_trackingHelper;
        $this->_storeManager = $_storeManager;
        //$this->_rewardData = $_rewardData;
    }

    /**
     * Update reward points for customer
     *
     * @return boolean
     */
    public function save()
    {
        // Do not process if reward points not enabled.
        $customerId = $this->_request->getParam('customer_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerData = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        $storeId = $this->getStoreId();
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        //if (!$this->_rewardData->isEnabledOnFront($websiteId)) {
          //  return false;
        //}

        /*$rewardFactory = $objectManager->create('Magento\Reward\Model\Reward');

        if (null !== $customerData) {
            $rewardFactory->setCustomerId($customerId)
                ->setStore($storeId)
                ->setAction(self::REWARD_ACTION_REVIEW)
                ->updateRewardPoints();
            return true;
        }*/

        return false;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
