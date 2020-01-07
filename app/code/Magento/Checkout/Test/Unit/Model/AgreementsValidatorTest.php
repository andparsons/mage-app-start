<?php
namespace Magento\Checkout\Test\Unit\Model;

class AgreementsValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\AgreementsValidator
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Checkout\Model\AgreementsValidator();
    }

    public function testIsValid()
    {
        $this->assertEquals(true, $this->model->isValid());
    }
}
