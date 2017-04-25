<?php

namespace Targetbay\Tracking\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    /**
     * Magento version configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'stage',
                'label' => __('Stage')
            ],
            [
                'value' => 'app',
                'label' => __('Production')
            ]
        ];
    }
}
