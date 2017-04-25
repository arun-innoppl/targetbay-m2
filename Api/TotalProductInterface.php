<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the porduct interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface TotalProductInterface
{
    /**
     * Return the total product counts.
     *
     * @return int.
     */
    public function totalproductcount();
}
