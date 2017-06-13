<?php

namespace Targetbay\Tracking\Block;

class Recommended extends \Magento\Framework\View\Element\Template
{
    public $trackingHelper;
    public $request;
    public $cmsPage;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Targetbay\Tracking\Helper\Data $trackingHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Cms\Model\Page $cmsPage
    ) {
        parent::__construct($context);
        $this->_trackingHelper = $trackingHelper;
        $this->_request = $request;
        $this->_cmsPage = $cmsPage;
        if ($this->_trackingHelper->trackingEnabled()) {
            $this->setTemplate('Targetbay_Tracking::recommended.phtml');
        }
    }

    public function getMostReviewedPlaceholder()
    {
        $routeName = $this->_request->getRouteName();
        $identifier = $this->_cmsPage->getIdentifier();

        $htmlTag = '';

        if ($routeName == 'cms' && $identifier == 'home') {
            $htmlTag = '<div id="tb_most_reviewed"></div>';
        }

        return $htmlTag;
    }
}
