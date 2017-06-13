<?php

namespace Targetbay\Tracking\Block\Product;

class Inventary extends \Magento\Framework\View\Element\Template
{ 
    protected $trackingHelper;
    protected $trackingInventaryHelper;
    protected $registry;
    protected $stockItemRepository;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Targetbay\Tracking\Helper\Data $trackingHelper,
            \Targetbay\Tracking\Helper\Inventary $trackingInventaryHelper,
            \Magento\Framework\Registry $registry,
            \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
        )
    {
        parent::__construct($context);
        $this->trackingHelper = $trackingHelper;
        $this->trackingInventaryHelper = $trackingInventaryHelper;
        $this->registry = $registry;
        $this->stockItemRepository = $stockItemRepository;
        /*if ((int)$this->trackingHelper->trackingEnabled()) {
            $this->setTemplate('Targetbay_Tracking::product/inventary.phtml');
        }*/
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getStockItem($productId)
    {
        return $this->stockItemRepository->get($productId);
    }

    public function getBackOrderStatus() {
        $html = '';
        $_product = $this->getProduct();
        $productId = $_product->getId();
        $stockDetails = $this->getStockItem($productId);
        if($this->trackingInventaryHelper->getInventryStatus() == 1) {
            $html .= '<div id="tb-backinstock"></div>'; 
        }
        return $html;
    }
}
