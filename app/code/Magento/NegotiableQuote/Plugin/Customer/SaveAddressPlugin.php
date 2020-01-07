<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Plugin\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Plugin for negotiable quotes address save.
 */
class SaveAddressPlugin
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $negotiableQuoteAddress;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * Authorization level of a company session.
     */
    private static $negotiableQuoteResource = 'Magento_NegotiableQuote::manage';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\AuthorizationInterface|null $authorization
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Api\AuthorizationInterface $authorization
    ) {
        $this->context = $context;
        $this->negotiableQuoteAddress = $negotiableQuoteAddress;
        $this->logger = $logger;
        $this->authorization = $authorization;
    }

    /**
     * Around save plugin that checks if negotiable quote with this shipping address should be recalculated.
     *
     * @param AddressRepositoryInterface $subject
     * @param AddressInterface $address
     * @return AddressInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        AddressRepositoryInterface $subject,
        AddressInterface $address
    ): AddressInterface {
        $quoteId = (int)$this->context->getRequest()->getParam('quoteId');
        if ($quoteId && $this->authorization->isAllowed(self::$negotiableQuoteResource)) {
            $this->saveQuoteAddress($address, $quoteId);
        }

        return $address;
    }

    /**
     * Save Negotiable quote address.
     *
     * @param AddressInterface $address
     * @param int $quoteId
     * @return void
     */
    private function saveQuoteAddress(AddressInterface $address, int $quoteId)
    {
        try {
            $this->negotiableQuoteAddress->updateQuoteShippingAddress($quoteId, $address);
        } catch (NoSuchEntityException $e) {
            $this->context->getMessageManager()->addErrorMessage(__('Requested quote was not found'));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->context->getMessageManager()->addErrorMessage(__('Unable to update shipping address'));
        }
    }
}
