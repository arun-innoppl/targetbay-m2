<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the wishlist item interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface TotalWishlistInterface
{
    /**
     * Return the total wishlist item counts.
     *
     * @return int.
     */
    public function totalwishlistcount();
}
