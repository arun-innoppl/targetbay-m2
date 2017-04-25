<?php

/**
 * @author Targetbay Team
 * @copyright Copyright (c) 2016 Targetbay
 * @package Targetbay_Tracking
 */

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalReviewInterface;

/**
 * Defines the implementation class of the TotalReview.
 */
class TotalReview implements TotalReviewInterface
{

    /**
     * Get the Total Review
     *
     * @return totals
     */
    public function totalreviewcount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $reviewFactory = $objectManager->create('Magento\Review\Model\ReviewFactory');

        $collection = $reviewFactory->create()->getCollection();

        $totals = [
            'total_review' => $collection->getSize()
        ];

        return json_encode($totals);
    }
}
