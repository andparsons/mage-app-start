<?php
namespace Magento\Checkout\Test\Unit\Helper;

class ExpressRedirectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_actionFlag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * Customer session
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    /**
     * @var \Magento\Checkout\Helper\ExpressRedirect
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_actionFlag = $this->getMockBuilder(
            \Magento\Framework\App\ActionFlag::class
        )->disableOriginalConstructor()->setMethods(
            ['set']
        )->getMock();

        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->_customerSession = $this->getMockBuilder(
            \Magento\Customer\Model\Session::class
        )->disableOriginalConstructor()->setMethods(
            ['setBeforeAuthUrl']
        )->getMock();

        $this->_context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helper = new \Magento\Checkout\Helper\ExpressRedirect(
            $this->_actionFlag,
            $this->_objectManager,
            $this->_customerSession,
            $this->_context
        );
    }

    /**
     * @dataProvider redirectLoginDataProvider
     * @param array $actionFlagList
     * @param string|null $customerBeforeAuthUrl
     * @param string|null $customerBeforeAuthUrlDefault
     */
    public function testRedirectLogin($actionFlagList, $customerBeforeAuthUrl, $customerBeforeAuthUrlDefault)
    {
        $expressRedirectMock = $this->getMockBuilder(
            \Magento\Checkout\Controller\Express\RedirectLoginInterface::class
        )->disableOriginalConstructor()->setMethods(
            [
                'getActionFlagList',
                'getResponse',
                'getCustomerBeforeAuthUrl',
                'getLoginUrl',
                'getRedirectActionName',
            ]
        )->getMock();
        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getActionFlagList'
        )->will(
            $this->returnValue($actionFlagList)
        );

        $atIndex = 0;
        $actionFlagList = array_merge(['no-dispatch' => true], $actionFlagList);
        foreach ($actionFlagList as $actionKey => $actionFlag) {
            $this->_actionFlag->expects($this->at($atIndex))->method('set')->with('', $actionKey, $actionFlag);
            $atIndex++;
        }

        $expectedLoginUrl = 'loginURL';
        $expressRedirectMock->expects(
            $this->once()
        )->method(
            'getLoginUrl'
        )->will(
            $this->returnValue($expectedLoginUrl)
        );

        $urlMock = $this->getMockBuilder(
            \Magento\Framework\Url\Helper\Data::class
        )->disableOriginalConstructor()->setMethods(
            ['addRequestParam']
        )->getMock();
        $urlMock->expects(
            $this->once()
        )->method(
            'addRequestParam'
        )->with(
            $expectedLoginUrl,
            ['context' => 'checkout']
        )->will(
            $this->returnValue($expectedLoginUrl)
        );

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Url\Helper\Data::class
        )->will(
            $this->returnValue($urlMock)
        );

        $responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->setMethods(
            ['setRedirect', '__wakeup']
        )->getMock();
        $responseMock->expects($this->once())->method('setRedirect')->with($expectedLoginUrl);

        $expressRedirectMock->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));

        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getCustomerBeforeAuthUrl'
        )->will(
            $this->returnValue($customerBeforeAuthUrl)
        );
        $expectedCustomerBeforeAuthUrl = $customerBeforeAuthUrl !== null
        ? $customerBeforeAuthUrl : $customerBeforeAuthUrlDefault;
        if ($expectedCustomerBeforeAuthUrl) {
            $this->_customerSession->expects(
                $this->once()
            )->method(
                'setBeforeAuthUrl'
            )->with(
                $expectedCustomerBeforeAuthUrl
            );
        }
        $this->_helper->redirectLogin($expressRedirectMock, $customerBeforeAuthUrlDefault);
    }

    /**
     * Data provider
     * @return array
     */
    public function redirectLoginDataProvider()
    {
        return [
            [[], 'beforeCustomerUrl', 'beforeCustomerUrlDEFAULT'],
            [['actionKey' => true], null, 'beforeCustomerUrlDEFAULT'],
            [[], 'beforeCustomerUrl', null]
        ];
    }
}
