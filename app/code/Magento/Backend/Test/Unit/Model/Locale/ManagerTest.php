<?php
namespace Magento\Backend\Test\Unit\Model\Locale;

use Magento\Framework\Locale\Resolver;

/**
 * Class ManagerTest
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\TranslateInterface
     */
    protected $_translator;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    protected function setUp()
    {
        $this->_session = $this->createMock(\Magento\Backend\Model\Session::class);

        $this->_authSession = $this->createPartialMock(\Magento\Backend\Model\Auth\Session::class, ['getUser']);

        $this->_backendConfig = $this->getMockForAbstractClass(
            \Magento\Backend\App\ConfigInterface::class,
            [],
            '',
            false
        );

        $userMock = new \Magento\Framework\DataObject();

        $this->_authSession->expects($this->any())->method('getUser')->will($this->returnValue($userMock));

        $this->_translator = $this->getMockBuilder(\Magento\Framework\TranslateInterface::class)
            ->setMethods(['init', 'setLocale'])
            ->getMockForAbstractClass();

        $this->_translator->expects($this->any())->method('setLocale')->will($this->returnValue($this->_translator));

        $this->_translator->expects($this->any())->method('init')->will($this->returnValue(false));

        $this->_model = new \Magento\Backend\Model\Locale\Manager(
            $this->_session,
            $this->_authSession,
            $this->_translator,
            $this->_backendConfig
        );
    }

    /**
     * @return array
     */
    public function switchBackendInterfaceLocaleDataProvider()
    {
        return ['case1' => ['locale' => 'de_DE'], 'case2' => ['locale' => 'en_US']];
    }

    /**
     * @param string $locale
     * @dataProvider switchBackendInterfaceLocaleDataProvider
     * @covers \Magento\Backend\Model\Locale\Manager::switchBackendInterfaceLocale
     */
    public function testSwitchBackendInterfaceLocale($locale)
    {
        $this->_model->switchBackendInterfaceLocale($locale);

        $userInterfaceLocale = $this->_authSession->getUser()->getInterfaceLocale();
        $this->assertEquals($userInterfaceLocale, $locale);

        $sessionLocale = $this->_session->getSessionLocale();
        $this->assertEquals($sessionLocale, null);
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceLocaleDefault()
    {
        $locale = $this->_model->getUserInterfaceLocale();

        $this->assertEquals($locale, Resolver::DEFAULT_LOCALE);
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceLocale()
    {
        $this->_model->switchBackendInterfaceLocale('de_DE');
        $locale = $this->_model->getUserInterfaceLocale();

        $this->assertEquals($locale, 'de_DE');
    }

    /**
     * @covers \Magento\Backend\Model\Locale\Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceGeneralLocale()
    {
        $this->_backendConfig->expects($this->any())
            ->method('getValue')
            ->with('general/locale/code')
            ->willReturn('test_locale');
        $locale = $this->_model->getUserInterfaceLocale();
        $this->assertEquals($locale, 'test_locale');
    }
}
