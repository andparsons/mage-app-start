<?php

namespace Magento\Company\Test\Unit\Plugin\Framework\Model\ActionValidator;

/**
 * Class RemoveActionPluginTest.
 */
class RemoveActionPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * @var \Magento\Company\Plugin\Framework\Model\ActionValidator\RemoveActionPlugin
     */
    private $removeActionPlugin;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockForAbstractClass(
            \Magento\Authorization\Model\UserContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getUserId']
        );
        $this->structureManager = $this->createPartialMock(
            \Magento\Company\Model\Company\Structure::class,
            ['getAllowedIds']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->removeActionPlugin = $objectManager->getObject(
            \Magento\Company\Plugin\Framework\Model\ActionValidator\RemoveActionPlugin::class,
            [
                'userContext' => $this->userContext,
                'structureManager' => $this->structureManager,
            ]
        );
    }

    /**
     * Test aroundIsAllowed method.
     *
     * @param int $customerId
     * @param int $currentCustomerId
     * @param bool $expectedResult
     * @return void
     * @dataProvider aroundIsAllowedDataProvider
     */
    public function testAroundIsAllowed($customerId, $currentCustomerId, $expectedResult)
    {
        $subject = $this->createMock(
            \Magento\Framework\Model\ActionValidator\RemoveAction::class
        );
        $model = $this->createPartialMock(
            \Magento\Customer\Model\Customer::class,
            ['getId']
        );
        $proceed = function ($model) {
            return false;
        };
        $model->expects($this->once())->method('getId')->willReturn($customerId);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($currentCustomerId);
        $this->structureManager->expects($this->once())->method('getAllowedIds')->willReturn(['users' => [1]]);

        $this->assertEquals($expectedResult, $this->removeActionPlugin->aroundIsAllowed($subject, $proceed, $model));
    }

    /**
     * Dara provider for aroundIsAllowed method.
     *
     * @return array
     */
    public function aroundIsAllowedDataProvider()
    {
        return [
            [1, 2, true],
            [1, 1, false],
        ];
    }
}
