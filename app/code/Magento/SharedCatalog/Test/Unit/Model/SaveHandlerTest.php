<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\SharedCatalog\Model\SaveHandler\DuplicatedPublicSharedCatalog;

/**
 * SaveHandler unit test.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var DuplicatedPublicSharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $duplicatedCatalogSaveHandler;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogSaveHandler;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler
     */
    private $saveHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerGroupManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\CustomerGroupManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validator = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->duplicatedCatalogSaveHandler = $this->getMockBuilder(DuplicatedPublicSharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogSaveHandler = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SaveHandler\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->saveHandler = $objectManager->getObject(
            \Magento\SharedCatalog\Model\SaveHandler::class,
            [
                'customerGroupManagement' => $this->customerGroupManagement,
                'sharedCatalogManagement' => $this->sharedCatalogManagement,
                'validator' => $this->validator,
                'duplicatedPublicCatalogSaveHandler' => $this->duplicatedCatalogSaveHandler,
                'catalogSaveHandler' => $this->catalogSaveHandler,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * Test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->expects($this->once())->method('validate')->with($sharedCatalog);
        $this->validator->expects($this->once())->method('isCatalogPublicTypeDuplicated')->with($sharedCatalog)
            ->willReturn(false);
        $this->catalogSaveHandler->expects($this->once())->method('execute')
            ->with($sharedCatalog, $sharedCatalog)->willReturn($sharedCatalog);
        $this->duplicatedCatalogSaveHandler->expects($this->never())->method('execute');

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->saveHandler->execute($sharedCatalog)
        );
    }

    /**
     * Test for execute() method if public catalog is duplicated.
     *
     * @return void
     */
    public function testExecuteIfPublicCatalogDuplicated()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->expects($this->once())->method('validate')->with($sharedCatalog);
        $this->validator->expects($this->once())->method('isCatalogPublicTypeDuplicated')->with($sharedCatalog)
            ->willReturn(true);
        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $this->duplicatedCatalogSaveHandler->expects($this->once())->method('execute')
            ->with($sharedCatalog, $publicCatalog)->willReturn($sharedCatalog);
        $this->catalogSaveHandler->expects($this->never())->method('execute');

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->saveHandler->execute($sharedCatalog)
        );
    }

    /**
     * Test execute with \Exception exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save shared catalog.
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception('exception message');
        $sharedCatalog = $this->prepareExecuteWithExceptions($exception);
        $this->logger->expects($this->once())->method('critical')->with('exception message');
        $sharedCatalog->expects($this->once())->method('setCustomerGroupId')->with(null);

        $this->saveHandler->execute($sharedCatalog);
    }

    /**
     * Test execute with CouldNotSaveException exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage exception message
     */
    public function testExecuteWithCouldNotSaveException()
    {
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__('exception message'));
        $sharedCatalog = $this->prepareExecuteWithExceptions($exception);
        $sharedCatalog->expects($this->once())->method('setCustomerGroupId')->with(null);

        $this->saveHandler->execute($sharedCatalog);
    }

    /**
     * Prepare mocks for execute() test with Exceptions.
     *
     * @param \Exception $exception
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareExecuteWithExceptions(\Exception $exception)
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator->expects($this->once())->method('validate')->with($sharedCatalog);
        $this->validator->expects($this->once())->method('isCatalogPublicTypeDuplicated')->with($sharedCatalog)
            ->willReturn(false);
        $this->catalogSaveHandler->expects($this->once())->method('execute')
            ->with($sharedCatalog, $sharedCatalog)->willThrowException($exception);
        $sharedCatalog->expects($this->exactly(2))->method('getCustomerGroupId')
            ->willReturnOnConsecutiveCalls(null, 1);
        $this->customerGroupManagement->expects($this->once())->method('deleteCustomerGroupById')->with($sharedCatalog);

        return $sharedCatalog;
    }

    /**
     * Test execute with CouldNotSaveException exception on roollback action.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save shared catalog.
     */
    public function testExecuteWithExceptionOnRollback()
    {
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__('exception message'));
        $sharedCatalog = $this->prepareExecuteWithExceptions($exception);
        $this->customerGroupManagement->expects($this->once())->method('deleteCustomerGroupById')->with($sharedCatalog)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with('exception message');
        $sharedCatalog->expects($this->never())->method('setCustomerGroupId');

        $this->saveHandler->execute($sharedCatalog);
    }
}
