<?php

namespace Magento\Company\Test\Unit\Model\Authorization;

/**
 * Class AclPolicyTest.
 */
class AclPolicyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Acl\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclBuilder;

    /**
     * @var \Magento\Company\Model\Authorization\AclPolicy
     */
    private $aclPolicyModel;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->aclBuilder = $this->createPartialMock(
            \Magento\Framework\Acl\Builder::class,
            ['getAcl']
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->aclPolicyModel = $objectManagerHelper->getObject(
            \Magento\Company\Model\Authorization\AclPolicy::class,
            [
                '_aclBuilder' => $this->aclBuilder
            ]
        );
    }

    /**
     * Test isAllowed method.
     *
     * @param int $roleId
     * @param int $resourceId
     * @param int $counter
     * @param bool $expectedResult
     * @return void
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($roleId, $resourceId, $counter, $expectedResult)
    {
        $acl = $this->createPartialMock(
            \Magento\Framework\Acl::class,
            ['isAllowed']
        );
        $this->aclBuilder->expects($this->exactly($counter))->method('getAcl')->willReturn($acl);
        $acl->expects($this->exactly($counter))->method('isAllowed')->willReturn(false);
        $this->assertEquals($expectedResult, $this->aclPolicyModel->isAllowed($roleId, $resourceId));
    }

    /**
     * Data provider for isAllowed method.
     *
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            [0, 1, 0, true],
            [1, 1, 1, false]
        ];
    }
}
