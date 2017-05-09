<?php

namespace Targetbay\Tracking\Block\Product;

class Richsnippets extends \Magento\Framework\View\Element\Template
{
    protected $trackingHelper;
    protected $registry;
    //protected $escaper;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Targetbay\Tracking\Helper\Data $trackingHelper,
            \Magento\Framework\Registry $registry
            //\Magento\Framework\Escaper $escaper
        )
    {
        parent::__construct($context);
        $this->trackingHelper = $trackingHelper;
        $this->registry = $registry;
        //$this->escaper = $escaper;
        if ((int)$this->trackingHelper->getRichsnippetType()) {
            $this->setTemplate('Targetbay_Tracking::product/richsnippets.phtml');
        }
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getEscapedProductName()
    {
        return $this->escapeHtml($this->getProduct()->getName());
    }

    public function getEscapedDescription()
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        return $this->escapeHtml($product->getDescription());
    }

    public function getSku()
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        return $product->getSku();
    }

    public function getProductReviews() {
        return $this->trackingHelper->getRichSnippets();
    }

    public function getSnippetType() {
        return $this->trackingHelper->getRichsnippetType();
    }
}
