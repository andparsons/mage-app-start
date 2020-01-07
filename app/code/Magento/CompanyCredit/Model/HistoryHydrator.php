<?php

namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\Data\CreditLimitInterface;

/**
 * Hydrates HistoryInterface object with data.
 */
class HistoryHydrator
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Api\DataObjectHelper $objectHelper
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Api\DataObjectHelper $objectHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->userContext = $userContext;
        $this->priceCurrency = $priceCurrency;
        $this->objectHelper = $objectHelper;
        $this->serializer = $serializer;
    }

    /**
     * Hydrates new history entity with credit limit data.
     *
     * @param HistoryInterface $history
     * @param CreditLimitInterface $creditLimit
     * @param int $type
     * @param float $amount
     * @param string $currency [optional]
     * @param string $comment [optional]
     * @param array $systemComments [optional]
     * @param \Magento\Framework\DataObject|null $options [optional]
     * @return HistoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function hydrate(
        HistoryInterface $history,
        CreditLimitInterface $creditLimit,
        $type,
        $amount,
        $currency = '',
        $comment = '',
        array $systemComments = [],
        \Magento\Framework\DataObject $options = null
    ) {
        if (empty($currency)) {
            $currency = $creditLimit->getCurrencyCode();
        }

        $currencyBase = null;
        if (!empty($options)) {
            $this->objectHelper->populateWithArray(
                $history,
                $options->getData(),
                HistoryInterface::class
            );
            $history->unsetData(HistoryInterface::HISTORY_ID);
            if ($options->getData('currency_display') && $options->getData('currency_display') != $currency) {
                $currencyBase = $options->getData('currency_base');
                $currency = $options->getData('currency_display');
            }
        }

        $history->setCompanyCreditId($creditLimit->getId());
        $history->setBalance($creditLimit->getBalance());
        $history->setCreditLimit($creditLimit->getCreditLimit());
        $history->setAvailableLimit($creditLimit->getAvailableLimit());
        $history->setCurrencyCredit($creditLimit->getCurrencyCode());
        $history->setType($type);
        $history->setAmount($amount);
        $history->setCurrencyOperation($currency);
        $history->setComment($this->buildCommentString($comment, $systemComments));

        if (!$history->getUserId() || !$history->getUserType()) {
            $history->setUserId($this->userContext->getUserId());
            $history->setUserType($this->userContext->getUserType());
        }

        if (!empty($options) && $currencyBase) {
            $operationCurrency = $history->getCurrencyOperation();
            $creditCurrency = $history->getCurrencyCredit();
            $history->setRateCredit($this->getCurrencyRateByCurrencyCodes($currencyBase, $creditCurrency));
            $history->setRate($this->getCurrencyRateByCurrencyCodes($currencyBase, $operationCurrency));
        } else {
            $history->setRate($this->getCurrencyRate($history));
        }

        return $history;
    }

    /**
     * Create comment string for custom and system comments.
     *
     * @param string $comment
     * @param array $systemComments
     * @return string
     */
    private function buildCommentString($comment, array $systemComments)
    {
        $commentArray = [];
        if ($comment) {
            $commentArray['custom'] = $comment;
        }
        if ($systemComments) {
            $commentArray['system'] = $systemComments;
        }

        return $commentArray ? $this->serializer->serialize($commentArray) : '';
    }

    /**
     * Get currency rate for operation.
     *
     * @param HistoryInterface $history
     * @return float
     */
    private function getCurrencyRate(HistoryInterface $history)
    {
        $creditCurrency = $history->getCurrencyCredit();
        $operationCurrency = $history->getCurrencyOperation();
        if ($creditCurrency == $operationCurrency) {
            return 1;
        }

        /** @var \Magento\Directory\Model\Currency $creditCurrency */
        $creditCurrency = $this->priceCurrency->getCurrency(null, $creditCurrency);

        return $creditCurrency->getRate($operationCurrency);
    }

    /**
     * Get currency rate by currencies codes.
     *
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @return float|int
     */
    private function getCurrencyRateByCurrencyCodes($fromCurrencyCode, $toCurrencyCode)
    {
        if ($fromCurrencyCode == $toCurrencyCode) {
            return 1;
        }

        /** @var \Magento\Directory\Model\Currency $creditCurrency */
        $creditCurrency = $this->priceCurrency->getCurrency(null, $fromCurrencyCode);

        return $creditCurrency->getRate($toCurrencyCode);
    }
}
