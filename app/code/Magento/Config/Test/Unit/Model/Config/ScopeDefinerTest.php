<?php
namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ScopeDefinerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\ScopeDefiner
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            \Magento\Config\Model\Config\ScopeDefiner::class,
            ['request' => $this->_requestMock]
        );
    }

    public function testGetScopeReturnsDefaultScopeIfNoScopeDataIsSpecified()
    {
        $this->assertEquals(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $this->_model->getScope());
    }

    public function testGetScopeReturnsStoreScopeIfStoreIsSpecified()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValueMap([['website', null, 'someWebsite'], ['store', null, 'someStore']])
        );
        $this->assertEquals(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_model->getScope());
    }

    public function testGetScopeReturnsWebsiteScopeIfWebsiteIsSpecified()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValueMap([['website', null, 'someWebsite'], ['store', null, null]])
        );
        $this->assertEquals(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->_model->getScope());
    }
}
