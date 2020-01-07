<?php
namespace Magento\CompanyCredit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * Config provider for credit limit.
 */
class CompanyCreditPaymentConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * Name for payment method.
     */
    const METHOD_NAME = 'companycredit';

    /**
     * Name for config payment action, depending on order status.
     */
    const PAYMENT_ACTION_ORDER = 'order';

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var CreditCheckoutData
     */
    private $creditCheckoutData;

    /**
     * CompanyCreditPaymentConfigProvider constructor.
     *
     * @param UserContextInterface $userContext
     * @param CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param CreditCheckoutData $creditCheckoutData
     */
    public function __construct(
        UserContextInterface $userContext,
        CreditDataProviderInterface $creditDataProvider,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CompanyRepositoryInterface $companyRepository,
        \Magento\Framework\App\Action\Context $context,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        CreditCheckoutData $creditCheckoutData
    ) {
        $this->userContext = $userContext;
        $this->creditDataProvider = $creditDataProvider;
        $this->quoteRepository = $quoteRepository;
        $this->companyRepository = $companyRepository;
        $this->context = $context;
        $this->websiteCurrency = $websiteCurrency;
        $this->creditCheckoutData = $creditCheckoutData;
    }

    /**
     * Get credit limit config.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $config = [];

        if ($companyId = $this->creditCheckoutData->getCompanyId()) {
            $quote = $this->getQuote();
            $creditLimit = $this->creditDataProvider->get($companyId);
            $quoteTotal = $this->creditCheckoutData->getGrandTotalInCreditCurrency(
                $quote,
                $creditLimit->getCurrencyCode()
            );
            $creditCurrency = $this->websiteCurrency->getCurrencyByCode($creditLimit->getCurrencyCode());
            $config = [
                'payment' => [
                    'companycredit' => [
                        'limit' => $creditLimit->getAvailableLimit(),
                        'exceedLimit' => $creditLimit->getExceedLimit(),
                        'limitFormatted' => $this->creditCheckoutData->formatPrice(
                            $creditLimit->getAvailableLimit(),
                            $creditCurrency
                        ),
                        'quoteTotalFormatted' => ($quoteTotal) ?
                            $this->creditCheckoutData->formatPrice($quoteTotal, $creditCurrency) : __('unavailable.'),
                        'exceededAmountFormatted' => $this->creditCheckoutData->formatPrice(
                            $quoteTotal - $creditLimit->getAvailableLimit(),
                            $creditCurrency
                        ),
                        'currency' => $creditLimit->getCurrencyCode(),
                        'rate' => $this->creditCheckoutData->getCurrencyConvertedRate(
                            $quote,
                            $creditLimit->getCurrencyCode()
                        ),
                        'baseRate' => $this->creditCheckoutData->getBaseRate($quote, $creditLimit->getCurrencyCode()),
                        'isBaseCreditCurrencyRateEnabled' => $this->creditCheckoutData->isBaseCreditCurrencyRateEnabled(
                            $quote,
                            $creditLimit->getCurrencyCode()
                        ),
                        'priceFormatPattern' => $this->creditCheckoutData->getPriceFormatPattern(
                            $creditLimit->getCurrencyCode()
                        ),
                        'companyName' => $this->companyRepository->get($companyId)->getCompanyName(),
                    ]
                ]
            ];
        }

        return $config;
    }

    /**
     * Get quote.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        if ($this->quote === null) {
            try {
                $this->quote = $this->quoteRepository->getActiveForCustomer($this->userContext->getUserId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $id = $this->context->getRequest()->getParam('negotiableQuoteId');
                $this->quote = $this->quoteRepository->get($id);
            }
        }

        return $this->quote;
    }
}
