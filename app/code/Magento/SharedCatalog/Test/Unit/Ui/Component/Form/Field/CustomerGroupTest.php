<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Form\Field;

/**
 * Unit test for CustomerGroup.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogManagement;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wrappedComponent;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Form\Field\CustomerGroup
     */
    private $groupField;

    /**
     * @var string
     */
    private $formElement = 'testElement';

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->catalogManagement = $this->createMock(
            \Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class
        );
        $this->groupManagement = $this->createMock(
            \Magento\Customer\Api\GroupManagementInterface::class
        );
        $this->moduleConfig = $this->createMock(\Magento\SharedCatalog\Model\Config::class);
        $processor = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['register', 'notify']
        );
        $context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getProcessor']
        );
        $context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $this->wrappedComponent = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setData', 'getContext']
        );
        $this->wrappedComponent->expects($this->once())->method('getContext')->willReturn($context);
        $this->uiComponentFactory =
            $this->createPartialMock(\Magento\Framework\View\Element\UiComponentFactory::class, ['create']);
        $this->uiComponentFactory->expects($this->once())->method('create')->willReturn($this->wrappedComponent);
        $data = ['config' => ['formElement' => $this->formElement]];
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->groupField = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Form\Field\CustomerGroup::class,
            [
                'catalogManagement' => $this->catalogManagement,
                'groupManagement' => $this->groupManagement,
                'moduleConfig' => $this->moduleConfig,
                'uiComponentFactory' => $this->uiComponentFactory,
                'storeManager' => $this->storeManager,
                'context' => $context,
                'components' => [],
                'data' => $data,
            ]
        );
    }

    /**
     * Test prepare method.
     *
     * @return void
     */
    public function testPrepare()
    {
        $publicGroupId = 1;
        $publicCatalog = $this->createMock(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $this->catalogManagement->expects($this->once())->method('getPublicCatalog')->willReturn($publicCatalog);
        $publicCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($publicGroupId);
        $this->groupManagement->expects($this->never())->method('getDefaultGroup');
        $this->wrappedComponent->expects($this->once())->method('setData')
            ->with(
                'config',
                [
                    'dataScope' => null,
                    'formElement' => $this->formElement,
                    'value' => $publicGroupId
                ]
            )->willReturnSelf();
        $this->groupField->prepare();
    }

    /**
     * Test prepare method with exception.
     *
     * @return void
     */
    public function testPrepareWithException()
    {
        $defaultGroupId = 1;
        $defaultGroup = $this->createMock(\Magento\Customer\Api\Data\GroupInterface::class);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $this->catalogManagement->expects($this->once())->method('getPublicCatalog')->willThrowException(
            new \Magento\Framework\Exception\NoSuchEntityException()
        );
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')->willReturn($defaultGroup);
        $defaultGroup->expects($this->once())->method('getId')->willReturn($defaultGroupId);
        $this->wrappedComponent->expects($this->once())->method('setData')
            ->with(
                'config',
                [
                    'dataScope' => null,
                    'formElement' => $this->formElement,
                    'value' => $defaultGroupId
                ]
            )->willReturnSelf();
        $this->groupField->prepare();
    }

    /**
     * Test prepare method with disabled module.
     *
     * @return void
     */
    public function testPrepareWithDisabledModule()
    {
        $defaultGroupId = 1;
        $defaultGroup = $this->createMock(\Magento\Customer\Api\Data\GroupInterface::class);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(false);
        $this->catalogManagement->expects($this->never())->method('getPublicCatalog');
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')->willReturn($defaultGroup);
        $defaultGroup->expects($this->once())->method('getId')->willReturn($defaultGroupId);
        $this->wrappedComponent->expects($this->once())->method('setData')
            ->with(
                'config',
                [
                    'dataScope' => null,
                    'formElement' => $this->formElement,
                    'value' => $defaultGroupId,
                    'notice' => null,
                ]
            )->willReturnSelf();
        $this->groupField->prepare();
    }
}
