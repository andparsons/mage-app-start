<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Purged;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ProviderTest.
 */
class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \Magento\NegotiableQuote\Model\PurgedContentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentFactory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->purgedContentFactory = $this->getMockBuilder(\Magento\NegotiableQuote\Model\PurgedContentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->provider = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Purged\Provider::class,
            [
                'purgedContentFactory' => $this->purgedContentFactory
            ]
        );
    }

    /**
     * Set up Purged Data Mock.
     *
     * @param string $purgedDataJson
     * @return void
     */
    private function setUpPurgedDataMock($purgedDataJson)
    {
        $purgedContent = $this->getMockBuilder(\Magento\NegotiableQuote\Model\PurgedContent::class)
            ->setMethods([
                'load',
                'getPurgedData'
            ])
            ->disableOriginalConstructor()->getMock();
        $purgedContent->expects($this->exactly(1))->method('load')->willReturnSelf();
        $purgedContent->expects($this->exactly(1))->method('getPurgedData')->willReturn($purgedDataJson);

        $this->purgedContentFactory->expects($this->exactly(1))->method('create')->willReturn($purgedContent);
    }

    /**
     * Test getCustomerName method.
     *
     * @return void
     */
    public function testGetCustomerName()
    {
        $quoteId = 23;
        $expected = 'Customer Name';
        $purgedData = sprintf('{"customer_name": "%s"}', $expected);
        $this->setUpPurgedDataMock($purgedData);

        $this->assertEquals($expected, $this->provider->getCustomerName($quoteId));
    }

    /**
     * Test getCompanyId method.
     *
     * @return void
     */
    public function testGetCompanyId()
    {
        $quoteId = 23;
        $expected = 89;
        $purgedData = sprintf('{"%s": "%s"}', \Magento\Company\Api\Data\CompanyInterface::COMPANY_ID, $expected);
        $this->setUpPurgedDataMock($purgedData);

        $this->assertEquals($expected, $this->provider->getCompanyId($quoteId));
    }

    /**
     * Test getCompanyName method.
     *
     * @return void
     */
    public function testGetCompanyName()
    {
        $quoteId = 23;
        $expected = 'Company Name';
        $purgedData = sprintf('{"%s": "%s"}', \Magento\Company\Api\Data\CompanyInterface::NAME, $expected);
        $this->setUpPurgedDataMock($purgedData);

        $this->assertEquals($expected, $this->provider->getCompanyName($quoteId));
    }

    /**
     * Test getCompanyEmail method.
     *
     * @return void
     */
    public function testGetCompanyEmail()
    {
        $quoteId = 23;
        $expected = 'company@test.com';
        $purgedData = sprintf('{"%s": "%s"}', \Magento\Company\Api\Data\CompanyInterface::EMAIL, $expected);
        $this->setUpPurgedDataMock($purgedData);

        $this->assertEquals($expected, $this->provider->getCompanyEmail($quoteId));
    }

    /**
     * Test getSalesRepresentativeId method.
     *
     * @return void
     */
    public function testGetSalesRepresentativeId()
    {
        $quoteId = 23;
        $expected = 99;
        $dataKey = \Magento\Company\Api\Data\CompanyInterface::SALES_REPRESENTATIVE_ID;
        $purgedData = sprintf('{"%s": "%s"}', $dataKey, $expected);
        $this->setUpPurgedDataMock($purgedData);

        $this->assertEquals($expected, $this->provider->getSalesRepresentativeId($quoteId));
    }

    /**
     * Test getSalesRepresentativeName method.
     *
     * @return void
     */
    public function testGetSalesRepresentativeName()
    {
        $quoteId = 23;
        $expected = 'Customer Name';
        $purgedData = sprintf('{"sales_representative_name": "%s"}', $expected);
        $this->setUpPurgedDataMock($purgedData);

        $this->assertEquals($expected, $this->provider->getSalesRepresentativeName($quoteId));
    }
}
