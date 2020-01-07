<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Company;

use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory;

/**
 * Test for \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassAssignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var CompanyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyCollectionFactoryMock;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyCollectionMock;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyStorageFactoryMock;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $companyStorageMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var MassAssign|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $massAssignController;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->contextMock = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, ['getRequest']);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->filterMock = $this->createPartialMock(\Magento\Ui\Component\MassAction\Filter::class, ['getCollection']);
        $this->resultJsonFactoryMock =
            $this->createPartialMock(\Magento\Framework\Controller\Result\JsonFactory::class, ['create']);
        $this->companyCollectionFactoryMock = $this->createPartialMock(
            \Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\CompanyFactory::class,
            ['create']
        );
        $this->companyCollectionMock = $this->createPartialMock(
            \Magento\SharedCatalog\Ui\DataProvider\Collection\Grid\Company::class,
            ['getItems']
        );
        $this->companyStorageFactoryMock =
            $this->createPartialMock(\Magento\SharedCatalog\Model\Form\Storage\CompanyFactory::class, ['create']);
        $this->companyStorageMock = $this->createPartialMock(
            \Magento\SharedCatalog\Model\Form\Storage\Company::class,
            ['assignCompanies', 'isCompanyAssigned']
        );
        $this->loggerMock = $this->getMockForAbstractClass(
            \Psr\Log\LoggerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['critical']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->massAssignController = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\MassAssign::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->companyCollectionFactoryMock,
                'companyStorageFactory' => $this->companyStorageFactoryMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test for method Execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $sameId = 12;
        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->withConsecutive(
                [UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY],
                ['is_assign']
            )
            ->willReturn($sameId);
        $this->companyStorageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->companyStorageMock);
        $json = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->companyCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->companyCollectionMock);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->companyCollectionMock)
            ->willReturn($this->companyCollectionMock);
        $this->companyCollectionMock->expects($this->once())->method('getItems');
        $this->companyStorageMock->expects($this->any())->method('assignCompanies')->willReturnSelf();
        $json->expects($this->any())->method('setJsonData')->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($json);

        $this->massAssignController->execute();
    }
}
