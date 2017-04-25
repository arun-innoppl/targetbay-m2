<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\ReviewRepoInterface;

/**
 * Defines the implementation class of the ReviewRepoInterface.
 */
class ReviewRepo implements ReviewRepoInterface
{
    /**
     * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
     */
    protected $_trackingHelper;

    /**
     * @param \Targetbay\Tracking\Helper\Data $trackingHelper
     */
    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper
    ) {
        $this->_trackingHelper = $_trackingHelper;
    }

    /**
     * Get review list
     * @return \Magento\Review\Api\Data\ReviewSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerFactory = $objectManager->create('\Magento\Customer\Model\CustomerFactory');
        $collection = $customerFactory->create()->getCollection();

        $collection->addNameToSelect();

        $collection->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left');

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $customer = $collection->load()->toArray();

        foreach ($customer as $id => $data) {
            $status = $this->_trackingHelper->getSubscriptionStatus($id);
            $customer[$id] ['subscription_status'] = $status;
        }

        return $customer;
    }
}
