<?php

namespace Targetbay\Tracking\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Yesno implements ArrayInterface
{
    /**
     * Magento email configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Yes')], ['value' => 2, 'label' => __('No')]];
    }
}
