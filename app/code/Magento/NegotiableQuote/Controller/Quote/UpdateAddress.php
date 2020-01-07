<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * UpdateAddress
 */
class UpdateAddress extends \Magento\NegotiableQuote\Controller\Quote implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const NEGOTIABLE_QUOTE_RESOURCE = 'Magento_NegotiableQuote::manage';

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Info
     */
    private $info;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address
     */
    private $negotiableQuoteAddress;

    /**
     * UpdateAddress constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Magento\NegotiableQuote\Block\Quote\Info $info
     * @param \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Magento\NegotiableQuote\Block\Quote\Info $info,
        \Magento\NegotiableQuote\Model\Quote\Address $negotiableQuoteAddress
    ) {
        parent::__construct(
            $context,
            $quoteHelper,
            $quoteRepository,
            $customerRestriction,
            $negotiableQuoteManagement,
            $settingsProvider
        );
        $this->info = $info;
        $this->negotiableQuoteAddress = $negotiableQuoteAddress;
    }

    /**
     * Change shipping address
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addErrorMessage('Wrong request.');
            $response = $this->settingsProvider->retrieveJsonError();
        } else {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $addressId = $this->getRequest()->getParam('address_id');
            try {
                $quote = $this->quoteRepository->get($quoteId);
                $this->customerRestriction->setQuote($quote);
                if ($this->customerRestriction->canSubmit()) {
                    $this->negotiableQuoteAddress->updateAddress($quoteId, $addressId);
                    $response = $this->settingsProvider->retrieveJsonSuccess(
                        ['addressHtml' => $this->info->getAddressHtml()]
                    );
                } else {
                    $response = $this->settingsProvider->retrieveJsonSuccess([]);
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Requested quote was not found'));
                $response = $this->settingsProvider->retrieveJsonError();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
                $response = $this->settingsProvider->retrieveJsonError();
            }
        }
        return $response;
    }
}
