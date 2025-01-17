<?php

namespace Magento\Payment\Test\Unit\Model\Source;

use \Magento\Payment\Model\Source\Invoice;

class InvoiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Invoice
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Invoice();
    }

    public function testToOptionArray()
    {
        $expectedResult = [
            [
                'value' => \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Yes'),
            ],
            ['value' => '', 'label' => __('No')],
        ];

        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
