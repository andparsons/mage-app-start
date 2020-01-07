<?php

namespace Magento\Company\Test\Unit\Model;

use Magento\Company\Model\Authorization;
use Magento\Framework\Authorization\PolicyInterface;
use Magento\Framework\Authorization\RoleLocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit tests for Magento\Company\Model\Authorization class.
 */
class AuthorizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @var PolicyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclPolicy;

    /**
     * @var RoleLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclRoleLocator;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->aclPolicy = $this->getMockBuilder(PolicyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->aclRoleLocator = $this->getMockBuilder(RoleLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
        $this->authorization = $objectManagerHelper->getObject(
            Authorization::class,
            [
                '_aclPolicy' => $this->aclPolicy,
                '_aclRoleLocator' => $this->aclRoleLocator,
            ]
        );
    }

    /**
     * Unit test for 'isAllowed' method.
     *
     * @param int $roleId
     * @param string $resourceId
     * @param bool $expectedResult
     * @return void
     *
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($roleId, $resourceId, $expectedResult)
    {
        $this->aclPolicy->expects($this->once())->method('isAllowed')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->authorization->isAllowed($roleId, $resourceId));
    }

    /**
     * Data provider for isAllowed method.
     *
     * @return array
     */
    public function isAllowedDataProvider()
    {
        return [
            [0, '1', false],
            [1, '1', true]
        ];
    }
}
