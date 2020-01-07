<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Company;

use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\Assign;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Class AssignTest
 */
class AssignTest extends \PHPUnit\Framework\TestCase
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
     * @var Assign|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assignController;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->contextMock = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, ['getRequest']);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->resultJsonFactoryMock =
            $this->createPartialMock(\Magento\Framework\Controller\Result\JsonFactory::class, ['create']);
        $this->companyStorageFactoryMock =
            $this->createPartialMock(\Magento\SharedCatalog\Model\Form\Storage\CompanyFactory::class, ['create']);
        $this->companyStorageMock = $this->createPartialMock(
            \Magento\SharedCatalog\Model\Form\Storage\Company::class,
            ['assignCompanies', 'isCompanyAssigned']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->assignController = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Company\Assign::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'companyStorageFactory' => $this->companyStorageFactoryMock
            ]
        );
    }

    /**
     * Test for method Execute
     */
    public function testExecute()
    {
        $sameId = 12;
        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->withConsecutive(
                [UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY],
                ['company_id'],
                ['is_assign']
            )
            ->willReturn($sameId);
        $this->companyStorageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->companyStorageMock);
        $json = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->companyStorageMock->expects($this->any())->method('assignCompanies')->willReturnSelf();
        $json->expects($this->any())->method('setJsonData')->willReturnSelf();
        $this->companyStorageMock->expects($this->any())->method('isCompanyAssigned')->willReturn(true);
        $this->resultJsonFactoryMock->expects($this->any())
            ->method('create')->willReturn($json);

        $this->assignController->execute();
    }
}
