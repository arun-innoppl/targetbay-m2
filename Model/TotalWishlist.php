<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalWishlistInterface;

/**
 * Defines the implementation class of the TotalWishlist.
 */
class TotalWishlist implements TotalWishlistInterface
{
    /**
     * Get the Total Wishlist
     *
     * @return totals
     */
    public function totalwishlistcount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wishlistCollection = $objectManager->create('Magento\Wishlist\Model\Wishlist')->getCollection();
        $i = 0;
        $count = '';

        foreach ($wishlistCollection as $id => $wishlist) {
            $wishlistInfo = $objectManager->create('Magento\Wishlist\Model\Wishlist')
                ->loadByCustomerId($wishlist->getCustomerId());
            $wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();
            if ($wishlistItemCollection->getSize() > 0) {
                $count = $i + 1;
                $i++;
            }
        }

        $totals = [
            'total_wishlist' => $count
        ];

        return json_encode($totals);
    }
}
