<?php

namespace Magento\CompanyCredit\Model\Email;

/**
 * Class Sender.
 */
class Sender
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\CompanyCredit\Model\Email\CompanyCreditDataFactory
     */
    private $companyCreditDataFactory;

    /**
     * @var \Magento\CompanyCredit\Model\Config\EmailTemplate
     */
    private $emailTemplateConfig;

    /**
     * @var NotificationRecipientLocator
     */
    private $notificationRecipient;

    /**
     * Email sender constructor.
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param CompanyCreditDataFactory $companyCreditDataFactory
     * @param \Magento\CompanyCredit\Model\Config\EmailTemplate $emailTemplateConfig
     * @param NotificationRecipientLocator $notificationRecipient
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger,
        CompanyCreditDataFactory $companyCreditDataFactory,
        \Magento\CompanyCredit\Model\Config\EmailTemplate $emailTemplateConfig,
        NotificationRecipientLocator $notificationRecipient
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->companyCreditDataFactory = $companyCreditDataFactory;
        $this->emailTemplateConfig = $emailTemplateConfig;
        $this->notificationRecipient = $notificationRecipient;
    }

    /**
     * Notify company admin of company credit changes.
     *
     * @param \Magento\CompanyCredit\Model\HistoryInterface $history
     * @return $this
     */
    public function sendCompanyCreditChangedNotificationEmail(\Magento\CompanyCredit\Model\HistoryInterface $history)
    {
        try {
            $companySuperUser = $this->notificationRecipient->getByRecord($history);
            $storeId = $companySuperUser->getStoreId();
            if (!$storeId) {
                $storeId = $this->emailTemplateConfig->getDefaultStoreId($companySuperUser);
            }
            $templateId = $this->emailTemplateConfig->getTemplateId($history->getType(), $storeId);
            if ($this->emailTemplateConfig->canSendNotification($companySuperUser) && $templateId) {
                $copyTo = $this->emailTemplateConfig->getCreditChangeCopyTo();
                $copyMethod = $this->emailTemplateConfig->getCreditCreateCopyMethod();
                $sendTo = $this->getSendTo($copyTo, $copyMethod, $companySuperUser);
                $companyCreditData = $this->companyCreditDataFactory->getCompanyCreditDataObject(
                    $history,
                    $companySuperUser
                );
                if ($companyCreditData !== null) {
                    foreach ($sendTo as $recipient) {
                        $this->sendEmailTemplate(
                            $recipient,
                            $companyCreditData->getData('customerName'),
                            $templateId,
                            $this->emailTemplateConfig->getSenderByStoreId($storeId),
                            [
                                'companyCredit' => $companyCreditData
                            ],
                            $storeId,
                            ($copyTo && $copyMethod == 'bcc') ? explode(',', $copyTo) : []
                        );
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        }

        return $this;
    }

    /**
     * Get copyTo email array.
     *
     * @param string $copyTo
     * @param string $copyMethod
     * @param \Magento\Customer\Api\Data\CustomerInterface $companySuperUser
     * @return array
     */
    private function getSendTo($copyTo, $copyMethod, $companySuperUser)
    {
        $sendTo = [];
        if ($copyTo && $copyMethod == 'copy') {
            $sendTo = explode(',', $copyTo);
        }
        array_unshift($sendTo, $companySuperUser->getEmail());

        return $sendTo;
    }

    /**
     * Send corresponding email template.
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param string $templateId
     * @param string|array $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string|array $bcc
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmailTemplate(
        $customerEmail,
        $customerName,
        $templateId,
        $sender,
        array $templateParams = [],
        $storeId = null,
        $bcc = []
    ) {
        $this->transportBuilder->setTemplateIdentifier($templateId);
        $this->transportBuilder->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        );
        $this->transportBuilder->setTemplateVars($templateParams);
        $this->transportBuilder->setFrom($sender);
        $this->transportBuilder->addTo($customerEmail, $customerName);
        $this->transportBuilder->addBcc($bcc);
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
