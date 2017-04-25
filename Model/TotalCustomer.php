<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalCustomerInterface;

/**
 * Defines the implementation class of the TotalCustomer.
 */
class TotalCustomer implements TotalCustomerInterface
{

    /**
     * Get the Total Customer
     *
     * @return totals
     */
    public function totalcustomercount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $customerFactory = $objectManager->create('Magento\Customer\Model\CustomerFactory');

        $collection = $customerFactory->create()->getCollection();

        $totals = [
            'total_customer' => $collection->getSize()
        ];

        return json_encode($totals);
    }
}
