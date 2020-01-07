<?php
namespace Magento\Customer\Model;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Form
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Form::class
        );
        $this->_model->setFormCode('customer_account_create');
    }

    public function testGetAttributes()
    {
        $attributes = $this->_model->getAttributes();
        $this->assertInternalType('array', $attributes);
        $this->assertNotEmpty($attributes);
    }
}
