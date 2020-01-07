<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Company\Model;

/**
 * Class CompanyTest
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Company\Model\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyPlugin;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyHelper;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->companyHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Company::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Company\Model\Company::class,
            [
                'companyHelper' => $this->companyHelper,
            ]
        );
    }

    /**
     * Test for method afterLoad
     */
    public function testAfterLoad()
    {
        $subject = $this->getMockForAbstractClass(\Magento\Company\Api\Data\CompanyInterface::class, [], '', false);
        $company = $this->getMockForAbstractClass(\Magento\Company\Api\Data\CompanyInterface::class, [], '', false);
        $this->companyHelper->expects($this->once())->method('loadQuoteConfig')->willReturn($company);

        $this->assertEquals($company, $this->companyPlugin->afterLoad($subject, $company));
    }
}
