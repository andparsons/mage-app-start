<?php
namespace Magento\AdminGws\Test\Unit\Model\Plugin;

use Magento\AdminGws\Block\Adminhtml\Permissions\Tab\Rolesedit\Gws;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole as SaveRoleController;

/**
 * Test class for \Magento\AdminGws\Model\Plugin\SaveRole testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveRoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AdminGws\Model\Plugin\SaveRole
     */
    private $model;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    /**
     * Request object
     *
     * @var \Magento\Backend\Model\Session
     */
    private $backendSessionMock;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultRedirectMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\Response\RedirectInterface::class,
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->session = new \Magento\Framework\DataObject();

        $this->backendSessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Session::class,
            ['setData', 'getData']
        );

        $this->backendSessionMock->method('setData')
            ->willReturnCallback([$this->session, 'setData']);

        $this->backendSessionMock->method('getData')
            ->willReturnCallback(
                function ($key) {
                    return $this->session->getData($key);
                }
            );

        $this->model = $objectManager->getObject(
            \Magento\AdminGws\Model\Plugin\SaveRole::class,
            [
                'resultRedirect' => $this->resultRedirectMock,
                'request' => $this->requestMock,
                'backendSession' => $this->backendSessionMock,
            ]
        );
    }

    /**
     * Checks a test case when to save post data in the session.
     *
     * @param array $postData
     * @param array $sessionDataBefore
     * @param array $sessionDataAfter
     * @dataProvider afterExecuteDataProvider
     */
    public function testAfterExecute(array $postData, array $sessionDataBefore, array $sessionDataAfter)
    {
        $this->session->setData($sessionDataBefore);
        $saveRoleController = $this->createMock(SaveRoleController::class);
        $result = $this->createMock(Redirect::class);
        $this->requestMock->expects($this->atMost(1))
            ->method('getPostValue')
            ->willReturn($postData);
        $this->assertEquals($result, $this->model->afterExecute($saveRoleController, $result));
        $this->assertEquals($sessionDataAfter, $this->session->getData());
    }

    /**
     * @return array
     */
    public function afterExecuteDataProvider()
    {
        return [
            'should save post data in the session when validation failed' => [
                'post_data' => [
                    'gws_is_all' => 'gws_is_all_value',
                    'gws_websites' => 'gws_websites_value',
                    'gws_store_groups' => 'gws_store_groups_value',
                ],
                'session_data_before' => [
                    SaveRoleController::ROLE_EDIT_FORM_DATA_SESSION_KEY => 1
                ],
                'session_data_after' => [
                    Gws::SCOPE_ALL_FORM_DATA_SESSION_KEY => 'gws_is_all_value',
                    Gws::SCOPE_WEBSITE_FORM_DATA_SESSION_KEY => 'gws_websites_value',
                    Gws::SCOPE_STORE_FORM_DATA_SESSION_KEY => 'gws_store_groups_value',
                    SaveRoleController::ROLE_EDIT_FORM_DATA_SESSION_KEY => 1
                ]
            ],
            'should not save post data in the session when validation passed' => [
                'post_data' => [
                    'gws_is_all' => 'gws_is_all_value',
                    'gws_websites' => 'gws_websites_value',
                    'gws_store_groups' => 'gws_store_groups_value',
                ],
                'session_data_before' => [
                    'some_key' => 'some_value'
                ],
                'session_data_after' => [
                    'some_key' => 'some_value'
                ]
            ]
        ];
    }
}
