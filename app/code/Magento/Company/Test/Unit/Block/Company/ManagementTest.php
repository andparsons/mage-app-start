<?php

namespace Magento\Company\Test\Unit\Block\Company;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\AuthorizationInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\RoleManagementInterface;
use Magento\Company\Block\Company\Management as CompanyManagement;
use Magento\Company\Model\Company\Structure as CompanyStructure;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree\Node as TreeNode;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;

/**
 * Unit tests for Magento\Company\Block\Company\Management class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var CompanyStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $treeManagement;

    /**
     * @var UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerContext;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var RoleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var CompanyManagement
     */
    private $management;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerContext = $this->getMockBuilder(UserContextInterface::class)
            ->setMethods(['getUserId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->treeManagement = $this->getMockBuilder(CompanyStructure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $jsonHelper = $this->getMockBuilder(JsonHelperData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->companyManagement = $this->getMockBuilder(CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->roleManagement = $this->getMockBuilder(RoleManagementInterface::class)
            ->setMethods(['getCompanyAdminRoleId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->authorization = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->management = $objectManagerHelper->getObject(
            CompanyManagement::class,
            [
                'customerContext' => $this->customerContext,
                'treeManagement' => $this->treeManagement,
                'jsonHelper' => $jsonHelper,
                '_urlBuilder' => $this->urlBuilder,
                'customerRepository' => $this->customerRepository,
                'companyManagement' => $this->companyManagement,
                'roleManagement' => $this->roleManagement,
                'authorization' => $this->authorization,
                'data' => [],
                'escaper' => $this->escaper,
            ]
        );
    }

    /**
     * Test getTreeJsOptions.
     *
     * @return void
     */
    public function testGetTreeJsOptions()
    {
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('*/structure/manage');

        $userRoleId = 0;
        $this->authorization->expects($this->atLeastOnce())->method('isAllowed')->willReturn(false);
        $treeOptionsArray =  [
            'hierarchyTree' => [
                'moveUrl'   => '*/structure/manage',
                'selectionLimit' => 1,
                'draggable' => false,
                'initData'  => '*/structure/manage',
                'adminUserRoleId' => $userRoleId,
            ]
        ];
        $this->assertEquals($treeOptionsArray, $this->management->getTreeJsOptions());
    }

    /**
     * Test isSuperUser.
     *
     * @return void
     */
    public function testIsSuperUser()
    {
        $this->authorization->expects($this->atLeastOnce())->method('isAllowed')->willReturn(false);

        $this->assertEquals(false, $this->management->isSuperUser());
    }

    /**
     * Test getTreePrepare.
     *
     * @return void
     */
    public function testGetTreePrepare()
    {
        $this->assertEquals([], $this->management->getTree());
    }

    /**
     * Test getTree.
     *
     * @param int $customerId
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $counter
     * @param bool $treeHasChildren
     * @param int $treeEntityId
     * @param array $result
     * @return void
     *
     * @dataProvider getTreeDataProvider
     */
    public function testGetTree(
        $customerId,
        \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $counter,
        $treeHasChildren,
        $treeEntityId,
        array $result
    ) {
        $this->customerContext->expects($this->atLeastOnce())->method('getUserId')->willReturn($customerId);
        $treeNode = $this->getMockBuilder(TreeNode::class)
            ->setMethods(['getData', 'hasChildren', 'getChildren'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper->expects($counter)->method('escapeHtml')->willReturnArgument(0);

        $treeNode->expects($counter)->method('hasChildren')->willReturnOnConsecutiveCalls($treeHasChildren, false);
        $treeNode->expects($counter)->method('getData')->willReturn($treeEntityId);
        $treeNode->expects($counter)->method('getChildren')->willReturnOnConsecutiveCalls([$treeNode], []);
        $this->treeManagement->expects($counter)->method('getTreeByCustomerId')->willReturn($treeNode);

        $this->assertEquals($result, $this->management->getTree());
    }

    /**
     * Test getJsonHelper.
     *
     * @return void
     */
    public function testGetJsonHelper()
    {
        $this->assertInstanceOf(JsonHelperData::class, $this->management->getJsonHelper());
    }

    /**
     * Test hasCustomerCompany.
     *
     * @param CompanyInterface|null $company
     * @param bool $result
     * @return void
     * @dataProvider hasCustomerCompanyDataProvider
     */
    public function testHasCustomerCompany($company, $result)
    {
        $this->customerContext->expects($this->exactly(1))->method('getUserId')->willReturn(1);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->with(1)->willReturn($company);
        $this->assertEquals($result, $this->management->hasCustomerCompany());
    }

    /**
     * Data provider dataProviderHasCustomerCompany.
     *
     * @return array
     */
    public function hasCustomerCompanyDataProvider()
    {
        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        return [
            [$company, true],
            [null, false]
        ];
    }

    /**
     * DataProvider getTree.
     *
     * @return array
     */
    public function getTreeDataProvider()
    {
        return [
            [
                0,
                $this->exactly(0),
                0,
                [],
                []
            ],
            [
                1,
                $this->atLeastOnce(),
                false,
                1,
                [
                    'type' => 'icon-company',
                    'text' => 1,
                    'description'=> 1,
                    'attr' => [
                        'data-tree-id' => 1,
                        'data-entity-id' => 1,
                        'data-entity-type' => 1
                    ]
                ]
            ],
            [
                1,
                $this->atLeastOnce(),
                true,
                1,
                [
                    'type' => 'icon-company',
                    'text' => 1,
                    'description'=> 1,
                    'attr' => [
                        'data-tree-id' => 1,
                        'data-entity-id' => 1,
                        'data-entity-type' => 1
                    ],
                    'state' => [
                        'opened' => true
                    ],
                    'children' => [
                        [
                            'type' => 'icon-company',
                            'text' => 1,
                            'description'=> 1,
                            'attr' => [
                                'data-tree-id' => 1,
                                'data-entity-id' => 1,
                                'data-entity-type' => 1
                            ],
                        ]
                    ]
                ]
            ],
            [
                1,
                $this->atLeastOnce(),
                true,
                2,
                [
                    'type' => 'icon-customer',
                    'text' => '2 2',
                    'attr' => [
                        'data-tree-id' => 2,
                        'data-entity-id' => 2,
                        'data-entity-type' => 2
                    ],
                    'state' => [
                        'opened' => true
                    ],
                    'children' => [
                        [
                            'type' => 'icon-customer',
                            'text' => '2 2',
                            'attr' => [
                                'data-tree-id' => 2,
                                'data-entity-id' => 2,
                                'data-entity-type' => 2
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
