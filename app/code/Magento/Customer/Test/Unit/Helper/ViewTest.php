<?php
namespace Magento\Customer\Test\Unit\Helper;

use Magento\Customer\Api\CustomerMetadataInterface;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadataService;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMetadataService = $this->createMock(\Magento\Customer\Api\CustomerMetadataInterface::class);

        $attributeMetadata = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attributeMetadata->expects($this->any())->method('isVisible')->will($this->returnValue(true));
        $this->customerMetadataService->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($attributeMetadata));

        $this->object = new \Magento\Customer\Helper\View($this->context, $this->customerMetadataService);
    }

    /**
     * @dataProvider getCustomerServiceDataProvider
     */
    public function testGetCustomerName($prefix, $firstName, $middleName, $lastName, $suffix, $result)
    {
        $customerData = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerData->expects($this->any())
            ->method('getPrefix')->will($this->returnValue($prefix));
        $customerData->expects($this->any())
            ->method('getFirstname')->will($this->returnValue($firstName));
        $customerData->expects($this->any())
            ->method('getMiddlename')->will($this->returnValue($middleName));
        $customerData->expects($this->any())
            ->method('getLastname')->will($this->returnValue($lastName));
        $customerData->expects($this->any())
            ->method('getSuffix')->will($this->returnValue($suffix));
        $this->assertEquals($result, $this->object->getCustomerName($customerData));
    }

    /**
     * @return array
     */
    public function getCustomerServiceDataProvider()
    {
        return [
            [
                'prefix', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'prefix first_name middle_name last_name suffix', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'first_name middle_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'prefix first_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                '', //suffix
                'prefix first_name middle_name last_name', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                'suffix', //suffix
                'first_name last_name suffix', //result name
            ],
            [
                'prefix', //prefix
                'first_name', //first_name
                '', //middle_name
                'last_name', //last_name
                '', //suffix
                'prefix first_name last_name', //result name
            ],
            [
                '', //prefix
                'first_name', //first_name
                'middle_name', //middle_name
                'last_name', //last_name
                '', //suffix
                'first_name middle_name last_name', //result name
            ],
        ];
    }
}
