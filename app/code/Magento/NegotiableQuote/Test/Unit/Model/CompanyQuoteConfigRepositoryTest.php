<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterfaceFactory;

/**
 * Class CompanyQuoteConfigRepositoryTest
 * @package Magento\NegotiableQuote\Test\Unit\Model
 */
class CompanyQuoteConfigRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\CompanyQuoteConfigRepository
     */
    private $repository;

    /**
     * @var CompanyQuoteConfigInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyQuoteConfigFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\CompanyQuoteConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyQuoteConfigResource;

    /**
     * \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyQuoteConfig;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->companyQuoteConfigFactory = $this->createMock(
            \Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterfaceFactory::class
        );
        $this->companyQuoteConfigResource = $this->createMock(
            \Magento\NegotiableQuote\Model\ResourceModel\CompanyQuoteConfig::class
        );
        $this->companyQuoteConfig = $this->createMock(
            \Magento\NegotiableQuote\Model\CompanyQuoteConfig::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->repository = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\CompanyQuoteConfigRepository::class,
            [
                'companyQuoteConfigFactory' => $this->companyQuoteConfigFactory,
                'companyQuoteConfigResource' => $this->companyQuoteConfigResource
            ]
        );
    }

    /**
     * Test for method save
     */
    public function testSave()
    {
        $this->companyQuoteConfigResource->expects($this->once())
            ->method('save')->with($this->companyQuoteConfig)->will($this->returnSelf());
        $this->assertEquals(true, $this->repository->save($this->companyQuoteConfig));
    }

    /**
     * Test for method save with CouldNotSaveException exception.
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage There was an error saving company quote config.
     * @return void
     */
    public function testSaveWithCouldNotSaveException()
    {
        $exception = new \Exception();
        $this->companyQuoteConfigResource->expects($this->once())
            ->method('save')->with($this->companyQuoteConfig)->willThrowException($exception);

        $this->repository->save($this->companyQuoteConfig);
    }
}
