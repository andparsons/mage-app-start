<?php

namespace Magento\CompanyCredit\Test\Unit\Model\Email;

/**
 * Class SenderTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\CompanyCredit\Model\Email\CompanyCreditDataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCreditDataFactory;

    /**
     * @var \Magento\CompanyCredit\Model\Config\EmailTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailTemplateConfig;

    /**
     * @var \Magento\CompanyCredit\Model\Email\NotificationRecipientLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationRecipient;

    /**
     * @var \Magento\CompanyCredit\Model\Email\Sender
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->transportBuilder = $this->createPartialMock(
            \Magento\Framework\Mail\Template\TransportBuilder::class,
            [
                'setFrom',
                'addTo',
                'getTransport',
                'addBcc',
                'setTemplateIdentifier',
                'setTemplateVars',
                'setTemplateOptions'
            ]
        );
        $this->logger = $this->createMock(
            \Psr\Log\LoggerInterface::class
        );
        $this->companyCreditDataFactory = $this->createMock(
            \Magento\CompanyCredit\Model\Email\CompanyCreditDataFactory::class
        );
        $this->emailTemplateConfig = $this->createMock(
            \Magento\CompanyCredit\Model\Config\EmailTemplate::class
        );
        $this->notificationRecipient = $this->createMock(
            \Magento\CompanyCredit\Model\Email\NotificationRecipientLocator::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CompanyCredit\Model\Email\Sender::class,
            [
                'transportBuilder' => $this->transportBuilder,
                'logger' => $this->logger,
                'companyCreditDataFactory' => $this->companyCreditDataFactory,
                'emailTemplateConfig' => $this->emailTemplateConfig,
                'notificationRecipient' => $this->notificationRecipient,
            ]
        );
    }

    /**
     * Test sendCompanyCreditChangedNotificationEmail method.
     *
     * @return void
     */
    public function testSendCompanyCreditChangedNotificationEmail()
    {
        $storeId = 1;
        $templateId = 'company_email_credit_allocated_email_template';
        $copyTo = 'info@example.com';
        $companyCreditData = new \Magento\Framework\DataObject();
        $history = $this->createMock(
            \Magento\CompanyCredit\Model\HistoryInterface::class
        );
        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $transport = $this->createMock(
            \Magento\Framework\Mail\TransportInterface::class
        );
        $this->notificationRecipient->expects($this->once())
            ->method('getByRecord')
            ->with($history)
            ->willReturn($customer);
        $customer->expects($this->once())->method('getStoreId')->willReturn(null);
        $this->emailTemplateConfig->expects($this->once())
            ->method('getDefaultStoreId')
            ->with($customer)
            ->willReturn($storeId);
        $history->expects($this->once())->method('getType')->willReturn(1);
        $this->emailTemplateConfig->expects($this->once())
            ->method('getTemplateId')
            ->with(1, $storeId)
            ->willReturn($templateId);
        $this->emailTemplateConfig->expects($this->once())
            ->method('canSendNotification')
            ->with($customer)
            ->willReturn(true);
        $this->emailTemplateConfig->expects($this->once())->method('getCreditChangeCopyTo')->willReturn($copyTo);
        $this->emailTemplateConfig->expects($this->once())->method('getCreditCreateCopyMethod')->willReturn('copy');
        $customer->expects($this->once())->method('getEmail')->willReturn('company_admin@example.com');
        $this->companyCreditDataFactory->expects($this->once())
            ->method('getCompanyCreditDataObject')
            ->with($history, $customer)
            ->willReturn($companyCreditData);
        $this->emailTemplateConfig->expects($this->exactly(2))
            ->method('getSenderByStoreId')
            ->with($storeId)
            ->willReturn('sales');
        $this->transportBuilder->expects($this->exactly(2))
            ->method('setTemplateIdentifier')
            ->with($templateId)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->exactly(2))
            ->method('setTemplateOptions')
            ->with(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->exactly(2))
            ->method('setTemplateVars')
            ->with(['companyCredit' => $companyCreditData])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->exactly(2))
            ->method('setFrom')
            ->with('sales')
            ->willReturnSelf();
        $this->transportBuilder->expects($this->exactly(2))
            ->method('addTo')
            ->withConsecutive(['company_admin@example.com'], ['info@example.com'])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->exactly(2))
            ->method('addBcc')
            ->with([])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->exactly(2))
            ->method('getTransport')
            ->willReturn($transport);
        $transport->expects($this->exactly(2))->method('sendMessage')->willReturnSelf();

        $this->model->sendCompanyCreditChangedNotificationEmail($history);
    }

    /**
     * Test sendCompanyCreditChangedNotificationEmail method throws exception.
     *
     * @return void
     */
    public function testSendCompanyCreditChangedNotificationEmailWithException()
    {
        $phrase = new \Magento\Framework\Phrase('Exception Message');
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $history = $this->createMock(
            \Magento\CompanyCredit\Model\HistoryInterface::class
        );
        $this->notificationRecipient->expects($this->once())
            ->method('getByRecord')
            ->with($history)
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception)->willReturnSelf();

        $this->model->sendCompanyCreditChangedNotificationEmail($history);
    }
}
