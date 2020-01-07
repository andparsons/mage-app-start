<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Unit\Model;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\RequisitionList\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->scopeConfig =
            $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->config = $objectManager->getObject(
            \Magento\RequisitionList\Model\Config::class,
            [
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * isActive() method test
     */
    public function testIsActive()
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag');
        $this->config->isActive();
    }

    /**
     * getMaxCountRequisitionList() method test
     */
    public function testGetMaxCountRequisitionList()
    {
        $maxCount = 123;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn((string)$maxCount);
        $actualResult = $this->config->getMaxCountRequisitionList();
        $this->assertEquals($maxCount, $actualResult);
    }
}
