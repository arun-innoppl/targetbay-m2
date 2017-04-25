<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CartItemRepoInterface;

/**
 * Defines the implementation class of the CartItemRepo.
 */
class CartItemRepo implements CartItemRepoInterface
{

    /**
     * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
     */
    protected $_trackingHelper;

    public function __construct(
        \Targetbay\Tracking\Helper\Data $_trackingHelper
    ) {
        $this->_trackingHelper = $_trackingHelper;
    }

    /**
     * get customer cart items.
     *
     * @return CartItemRepoInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {

        //$cartItems = $cartItemData = array();
        $quoteCollection = $this->getQuoteCollectionQuery($searchCriteria);
        $cartItems = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($quoteCollection as $id => $quoteInfo) {
            $quoteId = $quoteInfo['entity_id'];
            $quote = $objectManager->create('Magento\Quote\Model\Quote')->setId($quoteId);
            $items = $quote->getAllVisibleItems();
            if (count($items) > 0) {
                $cartItems[$id]['entity_id'] = $quoteInfo['entity_id'];
                $cartItems[$id]['customer_id'] = $quoteInfo['customer_id'];
                $cartItems[$id]['customer_email'] = $quoteInfo['customer_email'];
                $cartItems[$id]['abandonded_at'] = $quoteInfo['updated_at'];
                $cartItems[$id]['cart_items'] = $this->_trackingHelper->getQuoteItems($items);
            }
        }

        return $cartItems;
    }

    /**
     * get quote collection.
     *
     * @return CartItemRepoInterface[]
     */
    public function getQuoteCollectionQuery($searchCriteria)
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

        $quoteCollection->setCurPage($searchCriteria->getCurrentPage());
        $quoteCollection->setPageSize($searchCriteria->getPageSize());
        return $quoteCollection;
    }
}
