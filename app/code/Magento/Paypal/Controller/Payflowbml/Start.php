<?php

namespace Magento\Paypal\Controller\Payflowbml;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * Action for Bill Me Later checkout button (product view and shopping cart pages)
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward(
            'start',
            'payflowexpress',
            'paypal',
            [
                'bml' => 1,
                'button' => $this->getRequest()->getParam('button')
            ]
        );
    }
}
