<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Class CompanyTest.
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Company
     */
    protected $company;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->company = $objectManager->getObject(\Magento\Company\Model\Company::class);
    }

    public function testSaveAdvancedCustomAttributes()
    {
        $street = ['test', '123'];
        $this->company->setStreet($street);

        $this->assertEquals($street, $this->company->getStreet());
    }
}
