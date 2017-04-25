<?php

/**
 * @author TargetBay
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;

class EmailNotification extends \Magento\Customer\Model\EmailNotification
{
    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $trackingHelper = $objectManager->get('Targetbay\Tracking\Helper\Data');
        $customerModel = $objectManager->get('Magento\Customer\Model\Customer');

        $emailStatus = $trackingHelper->getEmailStatus();

        if ($emailStatus == 1) {
            return false;
        }
    }
}
