<?php

namespace Targetbay\Tracking\Block;

class Recommended extends \Magento\Framework\View\Element\Template
{
    protected $_trackingHelper;
    protected $_request;
    protected $_cmsPage;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Targetbay\Tracking\Helper\Data $_trackingHelper,
        \Magento\Framework\App\Request\Http $_request,
        \Magento\Cms\Model\Page $_cmsPage
    )
    {
        parent::__construct($context);
        $this->_trackingHelper = $_trackingHelper;
        $this->_request = $_request;
        $this->_cmsPage = $_cmsPage;
        if ($this->_trackingHelper->trackingEnabled()) {
            $this->setTemplate('Targetbay_Tracking::recommended.phtml');
        }
    }

    public function getMostReviewedPlaceholder() {
        $routeName = $this->_request->getRouteName();
        $identifier = $this->_cmsPage->getIdentifier();

        $htmlTag = '';

        if ($routeName == 'cms' && $identifier == 'home') {
            $htmlTag = '<div id="tb_most_reviewed"></div>';
        }

        return $htmlTag;
    }
}
