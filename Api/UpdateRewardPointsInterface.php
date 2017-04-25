<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the Update reward points for customer interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface UpdateRewardPointsInterface
{
    /**
     * Update reward points for customer
     * @throws \Exception
     * @return boolean
     */
    public function save();
}
