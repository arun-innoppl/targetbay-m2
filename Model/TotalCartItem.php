<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalCartItemInterface;

/**
 * Defines the implementation class of the TotalCartItem.
 */
class TotalCartItem implements TotalCartItemInterface
{

    /**
     * Get the Total Cart item
     *
     * @return totals
     */
    public function totalcartitemcount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->create('\Magento\Framework\App\ResourceConnection');

        $quoteTable = $connection->getTableName('quote_item');

        $quoteCollection = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Collection')
            ->addFieldToSelect([
                'customer_id',
                'customer_firstname',
                'customer_lastname',
                'customer_email',
                'updated_at'])
            ->addFieldToFilter('customer_email', ['neq' => ''])
            ->addFieldToFilter('customer_id', ['neq' => '']);

        $quoteCollection->getSelect()->join(['Q2' => $quoteTable], '`main_table`.`entity_id` = `Q2`.`quote_id`', ['*'])->group('Q2.quote_id');

        $totals = [
            'total_cartitem' => $quoteCollection->getSize()
        ];

        return json_encode($totals);
    }
}
