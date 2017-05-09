<?php

namespace Targetbay\Tracking\Block\Product;

class Review extends \Magento\Framework\View\Element\Template
{
    protected $trackingHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Targetbay\Tracking\Helper\Data $trackingHelper
    )
    {
        parent::__construct($context);
        $this->trackingHelper = $trackingHelper;
    }

    public function getQuestionReview() {
		return $this->trackingHelper->getQuestionSnippets();
    }
}
