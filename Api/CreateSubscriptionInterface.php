<?php

/** @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Api;

/**
 * Defines the create new subscription interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */
interface CreateSubscriptionInterface
{
    /**
     * create new newsletter subscription.
     * @throws \Exception
     * @return int
     */
    public function createsubscription();
}
