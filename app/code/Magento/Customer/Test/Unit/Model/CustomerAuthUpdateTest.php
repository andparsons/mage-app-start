<?php
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CustomerAuthUpdateTest
 */
class CustomerAuthUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerAuthUpdate
     */
    protected $model;

    /**
     * @var CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRegistry;

    /**
     * @var CustomerResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerResourceModel;

    /**
     * @var CustomerModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerModel;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerRegistry =
            $this->createMock(CustomerRegistry::class);
        $this->customerResourceModel =
            $this->createMock(CustomerResourceModel::class);
        $this->customerModel =
            $this->createMock(CustomerModel::class);

        $this->model = $this->objectManager->getObject(
            CustomerAuthUpdate::class,
            [
                'customerRegistry' => $this->customerRegistry,
                'customerResourceModel' => $this->customerResourceModel,
                'customerModel' => $this->customerModel
            ]
        );
    }

    /**
     * test SaveAuth
     * @throws NoSuchEntityException
     */
    public function testSaveAuth()
    {
        $customerId = 1;

        $customerSecureMock = $this->createMock(CustomerSecure::class);

        $dbAdapter = $this->createMock(AdapterInterface::class);

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->willReturn($customerSecureMock);

        $customerSecureMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturn(1);

        $this->customerResourceModel->expects($this->any())
            ->method('getConnection')
            ->willReturn($dbAdapter);

        $this->customerResourceModel->expects($this->any())
            ->method('getTable')
            ->willReturn('customer_entity');

        $dbAdapter->expects($this->any())
            ->method('update')
            ->with(
                'customer_entity',
                [
                    'failures_num' => 1,
                    'first_failure' => 1,
                    'lock_expires' => 1
                ]
            );

        $dbAdapter->expects($this->any())
            ->method('quoteInto')
            ->with(
                'entity_id = ?',
                $customerId
            );

        $this->customerModel->expects($this->once())
            ->method('reindex');

        $this->model->saveAuth($customerId);
    }
}
