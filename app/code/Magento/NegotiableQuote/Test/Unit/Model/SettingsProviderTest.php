<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Class SettingsProviderTest
 */
class SettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Customer\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerUrl;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider
     */
    private $settingsProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->moduleConfig = $this->createMock(\Magento\NegotiableQuote\Model\Config::class);
        $this->customerUrl = $this->createMock(\Magento\Customer\Model\Url::class);
        $this->resultJsonFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\JsonFactory::class, ['create']);
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->settingsProvider = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\SettingsProvider::class,
            [
                'moduleConfig' => $this->moduleConfig,
                'customerUrl' => $this->customerUrl,
                'resultJsonFactory' => $this->resultJsonFactory,
                'userContext' => $this->userContext,
            ]
        );
    }

    /**
     * Test isModuleEnabled
     *
     * @param bool $isModuleEnabled
     * @dataProvider dataProviderIsModuleEnabled
     */
    public function testIsModuleEnabled($isModuleEnabled)
    {
        $this->moduleConfig->expects($this->any())->method('isActive')->willReturn($isModuleEnabled);

        $this->assertEquals($isModuleEnabled, $this->settingsProvider->isModuleEnabled());
    }

    /**
     * Test getCustomerLoginUrl
     */
    public function testGetCustomerLoginUrl()
    {
        $customerUrl = 'customer_url';
        $this->customerUrl->expects($this->any())->method('getLoginUrl')->willReturn($customerUrl);

        $this->assertEquals($customerUrl, $this->settingsProvider->getCustomerLoginUrl());
    }

    /**
     * Test retrieveJsonSuccess
     */
    public function testRetrieveJsonSuccess()
    {
        $resultJson = $this->getResultJsonMock();

        $this->assertEquals($resultJson, $this->settingsProvider->retrieveJsonSuccess([]));
    }

    /**
     * Test retrieveJsonError
     */
    public function testRetrieveJsonError()
    {
        $resultJson = $this->getResultJsonMock();

        $this->assertEquals($resultJson, $this->settingsProvider->retrieveJsonError());
    }

    /**
     * Test getCurrentUserId
     */
    public function testGetCurrentUserId()
    {
        $userId = 1;
        $this->userContext->expects($this->any())->method('getUserId')->willReturn($userId);

        $this->assertEquals($userId, $this->settingsProvider->getCurrentUserId());
    }

    /**
     * getCurrentUserType
     */
    public function testGetCurrentUserType()
    {
        $userType = 'user_type';
        $this->userContext->expects($this->any())->method('getUserType')->willReturn($userType);

        $this->assertEquals($userType, $this->settingsProvider->getCurrentUserType());
    }

    /**
     * Test isModuleEnabled
     *
     * @return array
     */
    public function dataProviderIsModuleEnabled()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Get result json mock
     *
     * @return \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultJsonMock()
    {
        $resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $resultJson->expects($this->any())->method('setData')->willReturnSelf();
        $this->resultJsonFactory->expects($this->any())->method('create')->willReturn($resultJson);

        return $resultJson;
    }
}
