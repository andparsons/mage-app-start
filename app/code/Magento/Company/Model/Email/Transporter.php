<?php

namespace Magento\Company\Model\Email;

use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface as PsrLogger;

/**
 * Intermediate class for sending emails using transport
 */
class Transporter
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param TransportBuilder $transportBuilder
     * @param Escaper $escaper
     * @param PsrLogger $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        Escaper $escaper,
        PsrLogger $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Sends an email using transport
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param string|array $from
     * @param string $templateId
     * @param array $templateParams
     * @param int|null $storeId
     * @param array $bcc
     * @return void
     */
    public function sendMessage(
        $customerEmail,
        $customerName,
        $from,
        $templateId,
        array $templateParams = [],
        $storeId = null,
        $bcc = []
    ) {
        $templateParams = array_merge(
            $templateParams,
            ['escaper' => $this->escaper]
        );

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($customerEmail, $customerName)
            ->addBcc($bcc)
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }
}
