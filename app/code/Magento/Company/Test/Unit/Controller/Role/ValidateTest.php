<?php

namespace Magento\Company\Test\Unit\Controller\Role;

/**
 * Class ValidateTest.
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\CompanyUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUser;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Company\Controller\Role\Validate
     */
    private $validate;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyUser = $this->createMock(\Magento\Company\Model\CompanyUser::class);
        $this->roleRepository = $this->createMock(\Magento\Company\Model\RoleRepository::class);
        $this->searchCriteriaBuilder = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validate = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Role\Validate::class,
            [
                'companyUser' => $this->companyUser,
                'roleRepository' => $this->roleRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                '_request' => $this->request,
                'resultFactory' => $this->resultFactory,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $roleName = 'Role 1';
        $companyId = 2;
        $this->request->expects($this->once())->method('getParam')->with('company_role_name')->willReturn($roleName);
        $result = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $result->expects($this->once())->method('setData')->with(['company_role_name' => true])->willReturnSelf();
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $this->searchCriteriaBuilder->expects($this->at(0))->method('addFilter')->with(
            \Magento\Company\Api\Data\RoleInterface::ROLE_NAME,
            $roleName
        )->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->at(1))->method('addFilter')->with(
            \Magento\Company\Api\Data\RoleInterface::COMPANY_ID,
            $companyId
        )->willReturnSelf();
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $searchResults = $this->createMock(\Magento\Company\Api\Data\RoleSearchResultsInterface::class);
        $this->roleRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getTotalCount')->willReturn(0);
        $this->assertEquals($result, $this->validate->execute());
    }
}
