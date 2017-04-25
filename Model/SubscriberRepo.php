<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\SubscriberRepoInterface;

/**
 * Defines the implementation class of the SubscriberRepoInterface.
 */
class SubscriberRepo implements SubscriberRepoInterface
{
    /**
     * Get the list of subscribers
     *
     * @return SubscriberRepoInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $subscriberCollection = $objectManager->create('\Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory');

        $collection = $subscriberCollection->create()->addFieldToSelect('*');

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $customer = $collection->load()->toArray();

        return $customer;
    }
}
