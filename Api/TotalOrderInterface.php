<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the order interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface TotalOrderInterface
{
    /**
     * Return the total order counts.
     *
     * @return int.
     */
    public function totalordercount();
}
