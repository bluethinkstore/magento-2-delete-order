<?php
/**
 * Copyright © Bluethinkinc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\OrderDelete\Block\Adminhtml\Order\View;

class Buttons extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->getOrderId()) {
            return $this;
        }

        $buttonUrl = $this->_urlBuilder->getUrl(
            'order/index/delete',
            ['order_id' => $this->getOrderId()]
        );

        $this->addButton(
            'create_custom_button',
            ['label' => __('Delete'),
            'onclick'   => "confirmSetLocation('Are you sure you want to delete?', '{$buttonUrl}')"]
        );
        
        return $this;
    }
}
