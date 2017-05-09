<?php

namespace Targetbay\Tracking\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Snippetstatus implements ArrayInterface
{
    /**
     * Structured data option configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Please Select')
            ],
            [
                'value' => 0,
                'label' => __('Disable')
            ],
            [
                'value' => 1,
                'label' => __('Automatic')
            ]
        ];
    }
}
