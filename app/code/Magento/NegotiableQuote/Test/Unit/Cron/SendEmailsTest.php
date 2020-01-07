<?php

namespace Magento\NegotiableQuote\Test\Unit\Cron;

use Magento\NegotiableQuote\Cron\SendEmails;
use Magento\Store\Model\ScopeInterface;

/**
 * Unit test for Send Emails.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendEmailsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\EmailSenderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailSender;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDate;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\NegotiableQuote\Cron\SendEmails
     */
    private $cron;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteFactory = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->negotiableQuoteRepository = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList'])
            ->getMockForAbstractClass();
        $this->emailSender = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\EmailSenderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendChangeQuoteEmailToMerchant', 'sendChangeQuoteEmailToBuyer'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create'])
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteManagement = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote'])
            ->getMockForAbstractClass();
        $this->scopeConfig = $this
            ->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cron = $objectManager->getObject(
            \Magento\NegotiableQuote\Cron\SendEmails::class,
            [
                'negotiableQuoteFactory' => $this->negotiableQuoteFactory,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'emailSender' => $this->emailSender,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'localeDate' => $this->localeDate,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Test execute().
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $quoteId = 1;
        $currentDate = new \DateTime;
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(SendEmails::CONFIG_QUOTE_EMAIL_NOTIFICATIONS_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->localeDate->expects($this->any())->method('date')->willReturn($currentDate);
        $this->filterBuilder
            ->expects($this->exactly(4))
            ->method('setField')
            ->withConsecutive(
                ['extension_attribute_negotiable_quote.expiration_period'],
                ['extension_attribute_negotiable_quote.status_email_notification'],
                ['extension_attribute_negotiable_quote.expiration_period'],
                ['extension_attribute_negotiable_quote.status_email_notification']
            )
            ->willReturnSelf();
        $this->filterBuilder
            ->expects($this->exactly(4))
            ->method('setValue')
            ->willReturnSelf();
        $this->filterBuilder
            ->expects($this->exactly(4))
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturnSelf();
        $searchCriteria = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cart = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensibleData = $this
            ->getMockBuilder(\Magento\Framework\Api\ExtensibleDataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder
            ->expects($this->exactly(4))
            ->method('addFilters')
            ->willReturnSelf();
        $this->searchCriteriaBuilder
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($searchCriteria);
        $this->negotiableQuoteRepository
            ->expects($this->exactly(2))
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults
            ->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$extensibleData]);
        $extensibleData->expects($this->exactly(2))->method('getId')->willReturn($quoteId);
        $negotiableQuoteMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setQuoteId', 'setEmailNotificationStatus'])
            ->getMockForAbstractClass();
        $negotiableQuoteMock->expects($this->exactly(2))
            ->method('setQuoteId')
            ->with($quoteId)
            ->willReturnSelf();
        $negotiableQuoteMock->expects($this->exactly(2))
            ->method('setEmailNotificationStatus')
            ->withConsecutive(
                [SendEmails::EMAIL_SENT_TWO_DAYS_COUNTER],
                [SendEmails::EMAIL_SENT_ONE_DAY_COUNTER]
            )
            ->willReturnSelf();
        $this->negotiableQuoteFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($negotiableQuoteMock);
        $this->negotiableQuoteRepository->expects($this->exactly(2))
            ->method('save')
            ->with($negotiableQuoteMock)
            ->willReturn(true);
        $this->negotiableQuoteManagement->expects($this->exactly(2))
            ->method('getNegotiableQuote')
            ->with($quoteId)
            ->willReturn($cart);
        $this->emailSender->expects($this->once())
            ->method('sendChangeQuoteEmailToMerchant')
            ->with($cart, SendEmails::EXPIRE_TWO_DAYS_TEMPLATE)
            ->willReturnSelf();
        $this->emailSender->expects($this->once())
            ->method('sendChangeQuoteEmailToBuyer')
            ->with($cart, SendEmails::EXPIRE_ONE_DAY_TEMPLATE)
            ->willReturnSelf();

        $this->cron->execute();
    }
}
