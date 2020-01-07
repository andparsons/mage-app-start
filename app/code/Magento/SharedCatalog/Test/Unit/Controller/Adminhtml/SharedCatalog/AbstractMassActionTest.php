<?php
namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class AbstractMassActionTest
 * @package Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog
 */
class AbstractMassActionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject*/
    protected $context;

    /** @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject */
    protected $filter;

    /** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory
     * |\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactory;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManagerHelper;

    /** @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\AbstractMassAction */
    protected $abstractMassAction;

    protected function setUp()
    {
        $this->context = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getMessageManager', 'getResultFactory']
        );
        $this->filter = $this->createPartialMock(\Magento\Ui\Component\MassAction\Filter::class, ['getCollection']);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory::class,
            ['create']
        );
        $this->logger = $this->getMockForAbstractClass(
            \Psr\Log\LoggerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['critical']
        );
    }

    /**
     * Test for method Execute
     */
    public function testExecute()
    {
        $collection = $this->createMock(\Magento\Framework\Data\Collection\AbstractDb::class);
        $filteredCollection = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class,
            [],
            '',
            false
        );

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));

        $this->filter->expects($this->once())
            ->method('getCollection')
            ->with($collection)
            ->will($this->returnValue($filteredCollection));

        $this->abstractMassAction = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\AbstractMassAction::class,
            [
                $this->context,
                $this->filter,
                $this->collectionFactory,
                $this->logger,
            ],
            '',
            true,
            false,
            true,
            []
        );

        $this->abstractMassAction->expects($this->once())
            ->method('massAction')
            ->with($filteredCollection);

        $result = $this->abstractMassAction->execute();
        $this->assertNull($result);
    }

    /**
     * Test for method Execute
     */
    public function testExecuteException()
    {
        $sampleResult = 'sample result';
        $message = 'An Error has occured';
        $exception = new \Exception($message);
        $messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['addError']
        );
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->throwException($exception));
        $messageManager->expects($this->once())
            ->method('addError')
            ->with($message);
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $resultRedirect = $this->createPartialMock(\Magento\Backend\Model\View\Result\Redirect::class, ['setPath']);
        $resultRedirect->expects($this->any())
            ->method('setPath')
            ->will($this->returnValue($sampleResult));

        $resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->will($this->returnValue($resultRedirect));

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->will($this->returnValue($resultFactory));

        $this->abstractMassAction = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\AbstractMassAction::class,
            [
                $this->context,
                $this->filter,
                $this->collectionFactory,
                $this->logger,
            ],
            '',
            true,
            false,
            true,
            []
        );

        $result = $this->abstractMassAction->execute();
        $this->assertEquals($sampleResult, $result);
    }
}
