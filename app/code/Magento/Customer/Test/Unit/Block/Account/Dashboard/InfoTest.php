<?php

namespace Magento\Customer\Test\Unit\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Block\Account\Dashboard\Info;

/**
 * Test class for \Magento\Customer\Block\Account\Dashboard\Info.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /** Constant values used for testing */
    const CUSTOMER_ID = 1;

    const CHANGE_PASSWORD_URL = 'http://localhost/index.php/account/edit/changepass/1';

    const EMAIL_ADDRESS = 'john.doe@example.com';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\View\Element\Template\Context */
    private $_context;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Api\Data\CustomerInterface */
    private $_customer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\View
     */
    private $_helperView;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Newsletter\Model\Subscriber */
    private $_subscriber;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Newsletter\Model\SubscriberFactory */
    private $_subscriberFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Block\Form\Register */
    private $_formRegister;

    /** @var Info */
    private $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    protected function setUp()
    {
        $this->currentCustomer = $this->createMock(\Magento\Customer\Helper\Session\CurrentCustomer::class);

        $urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class, [], '', false);
        $urlBuilder->expects($this->any())->method('getUrl')->will($this->returnValue(self::CHANGE_PASSWORD_URL));

        $layout = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class, [], '', false);
        $this->_formRegister = $this->createMock(\Magento\Customer\Block\Form\Register::class);
        $layout->expects($this->any())
            ->method('getBlockSingleton')
            ->with(\Magento\Customer\Block\Form\Register::class)
            ->will($this->returnValue($this->_formRegister));

        $this->_context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $this->_context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        $this->_customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->_customerSession->expects($this->any())->method('getId')->will($this->returnValue(self::CUSTOMER_ID));

        $this->_customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->_customer->expects($this->any())->method('getEmail')->will($this->returnValue(self::EMAIL_ADDRESS));
        $this->_helperView = $this->getMockBuilder(
            \Magento\Customer\Helper\View::class
        )->disableOriginalConstructor()->getMock();
        $this->_subscriberFactory = $this->createPartialMock(
            \Magento\Newsletter\Model\SubscriberFactory::class,
            ['create']
        );
        $this->_subscriber = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $this->_subscriber->expects($this->any())->method('loadByEmail')->will($this->returnSelf());
        $this->_subscriberFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_subscriber));

        $this->_block = new \Magento\Customer\Block\Account\Dashboard\Info(
            $this->_context,
            $this->currentCustomer,
            $this->_subscriberFactory,
            $this->_helperView
        );
    }

    public function testGetCustomer()
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->will($this->returnValue($this->_customer));

        $customer = $this->_block->getCustomer();
        $this->assertEquals($customer, $this->_customer);
    }

    public function testGetCustomerException()
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'customerId', 'fieldValue' => 1]
                    )
                ))
            );

        $this->assertNull($this->_block->getCustomer());
    }

    public function testGetName()
    {
        $expectedValue = 'John Q Doe Jr';

        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->will($this->returnValue($this->_customer));

        /**
         * Called three times, once for each attribute (i.e. prefix, middlename, and suffix)
         */
        $this->_helperView->expects($this->any())->method('getCustomerName')->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $this->_block->getName());
    }

    public function testGetChangePasswordUrl()
    {
        $this->assertEquals(self::CHANGE_PASSWORD_URL, $this->_block->getChangePasswordUrl());
    }

    public function testGetSubscriptionObject()
    {
        $this->assertSame($this->_subscriber, $this->_block->getSubscriptionObject());
    }

    /**
     * @param bool $isSubscribed Is the subscriber subscribed?
     * @param bool $expectedValue The expected value - Whether the subscriber is subscribed or not.
     *
     * @dataProvider getIsSubscribedProvider
     */
    public function testGetIsSubscribed($isSubscribed, $expectedValue)
    {
        $this->_subscriber->expects($this->once())->method('isSubscribed')->will($this->returnValue($isSubscribed));
        $this->assertEquals($expectedValue, $this->_block->getIsSubscribed());
    }

    /**
     * @return array
     */
    public function getIsSubscribedProvider()
    {
        return [[true, true], [false, false]];
    }

    /**
     * @param bool $isNewsletterEnabled Determines if the newsletter is enabled
     * @param bool $expectedValue The expected value - Whether the newsletter is enabled or not
     *
     * @dataProvider isNewsletterEnabledProvider
     */
    public function testIsNewsletterEnabled($isNewsletterEnabled, $expectedValue)
    {
        $this->_formRegister->expects($this->once())
            ->method('isNewsletterEnabled')
            ->will($this->returnValue($isNewsletterEnabled));
        $this->assertEquals($expectedValue, $this->_block->isNewsletterEnabled());
    }

    /**
     * @return array
     */
    public function isNewsletterEnabledProvider()
    {
        return [[true, true], [false, false]];
    }
}
