<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for \Magento\Company\Model\CompanyProfile class.
 */
class CompanyProfileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectHelper;

    /**
     * @var \Magento\Company\Model\CompanyProfile
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectHelper = $this->createMock(
            \Magento\Framework\Api\DataObjectHelper::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\CompanyProfile::class,
            [
                'objectHelper' => $this->objectHelper,
            ]
        );
    }

    /**
     * Test populate method.
     *
     * @param array $data
     * @param array $companyData
     * @return void
     * @dataProvider populateDataProvider
     */
    public function testPopulate(array $data, array $companyData)
    {
        $company = $this->createMock(
            \Magento\Company\Api\Data\CompanyInterface::class
        );
        $this->objectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($company, $companyData, \Magento\Company\Api\Data\CompanyInterface::class)
            ->willReturnSelf();

        $this->model->populate($company, $data);
    }

    /**
     * Data provider for populate method.
     *
     * @return array
     */
    public function populateDataProvider()
    {
        return [
            [
                [
                    \Magento\Company\Api\Data\CompanyInterface::COUNTRY_ID => 'US',
                    \Magento\Company\Api\Data\CompanyInterface::REGION_ID => 12
                ],
                [
                    'country_id' => 'US',
                    'region_id' => 12
                ]
            ],
        ];
    }
}
