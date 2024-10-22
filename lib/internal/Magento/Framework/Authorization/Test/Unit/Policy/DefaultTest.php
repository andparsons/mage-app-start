<?php
namespace Magento\Framework\Authorization\Test\Unit\Policy;

class DefaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Authorization\Policy\DefaultPolicy
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Authorization\Policy\DefaultPolicy();
    }

    public function testIsAllowedReturnsTrueForAnyResource()
    {
        $this->assertTrue($this->_model->isAllowed('any_role', 'any_resource'));
    }
}
