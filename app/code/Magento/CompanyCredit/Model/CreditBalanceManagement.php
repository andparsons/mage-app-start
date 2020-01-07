<?php

namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\CreditBalanceManagementInterface;
use Magento\CompanyCredit\Api\CreditLimitRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Exception\InputException;
use Magento\CompanyCredit\Model\ResourceModel\CreditLimit as CreditLimitResource;

/**
 * Credit balance management: decrease and increase credit balance operations.
 */
class CreditBalanceManagement implements CreditBalanceManagementInterface
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitHistory
     */
    private $creditLimitHistory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit
     */
    private $creditLimitResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var array
     */
    private $decreaseTypes = [
        HistoryInterface::TYPE_PURCHASED,
        HistoryInterface::TYPE_REIMBURSED,
        HistoryInterface::TYPE_UPDATED
    ];

    /**
     * @var array
     */
    private $increaseTypes = [
        HistoryInterface::TYPE_REFUNDED,
        HistoryInterface::TYPE_REVERTED,
        HistoryInterface::TYPE_REIMBURSED,
        HistoryInterface::TYPE_UPDATED
    ];

    /**
     * CreditBalanceManagement constructor.
     *
     * @param CreditLimitRepositoryInterface $creditLimitRepository
     * @param CreditLimitHistory $creditLimitHistory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CreditLimitResource $creditLimitResource
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     */
    public function __construct(
        CreditLimitRepositoryInterface $creditLimitRepository,
        CreditLimitHistory $creditLimitHistory,
        PriceCurrencyInterface $priceCurrency,
        CreditLimitResource $creditLimitResource,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
    ) {
        $this->creditLimitRepository = $creditLimitRepository;
        $this->creditLimitHistory = $creditLimitHistory;
        $this->priceCurrency = $priceCurrency;
        $this->creditLimitResource = $creditLimitResource;
        $this->customerRepository = $customerRepository;
        $this->websiteCurrency = $websiteCurrency;
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\InputException
     */
    public function decrease(
        $creditId,
        $value,
        $currency,
        $operationType,
        $comment = '',
        \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface $options = null
    ) {
        $this->validate($this->decreaseTypes, $operationType, $value, $creditId, $currency);

        $this->changeCredit(
            $creditId,
            -$value,
            $currency,
            $comment,
            $operationType,
            $options
        );

        return true;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\InputException
     */
    public function increase(
        $creditId,
        $value,
        $currency,
        $operationType,
        $comment = '',
        \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface $options = null
    ) {
        $this->validate($this->increaseTypes, $operationType, $value, $creditId, $currency);

        $this->changeCredit(
            $creditId,
            $value,
            $currency,
            $comment,
            $operationType,
            $options
        );

        return true;
    }

    /**
     * Log credit balance changes in the history after balance change operation.
     *
     * @param int $balanceId
     * @param float $value
     * @param string $currency
     * @param string $comment
     * @param int $status
     * @param \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface|null $options [optional]
     * @return void
     * @throws \Exception
     */
    private function changeCredit(
        $balanceId,
        $value,
        $currency,
        $comment,
        $status,
        \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface $options = null
    ) {
        if ($status == HistoryInterface::TYPE_ALLOCATED) {
            $credit = $this->creditLimitRepository->get($balanceId);
            if ($credit->getCurrencyCode() != $currency) {
                /** @var \Magento\Directory\Model\Currency $creditCurrency */
                $creditCurrency = $this->priceCurrency->getCurrency(null, $credit->getCurrencyCode());
                $currencyRate = $creditCurrency->getRate($currency);
                $value = $currencyRate * $value;
            }
            $credit->setCreditLimit($credit->getCreditLimit() + $value);
            $credit->setData('credit_comment', $comment);
            $this->creditLimitRepository->save($credit);
        } else {
            $connection = $this->creditLimitResource->getConnection();
            $connection->beginTransaction();
            try {
                $this->creditLimitResource->changeBalance($balanceId, $value, $currency);

                $systemComments = [];
                if (!empty($options) && $options->getData('order_increment')) {
                    $systemComments['order'] = $options->getData('order_increment');
                }
                $credit = $this->creditLimitRepository->get($balanceId, true);
                $this->creditLimitHistory->logCredit(
                    $credit,
                    $status,
                    $value,
                    $currency,
                    $comment,
                    $systemComments,
                    $options
                );
                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Validates status, value and balance availability for credit balance change operation.
     *
     * @param array $increaseTypes
     * @param int $operationType
     * @param float $value
     * @param int $balanceId
     * @param string $currency
     * @throws InputException
     * @return void
     */
    private function validate(array $increaseTypes, $operationType, $value, $balanceId, $currency)
    {
        if ((!in_array($operationType, $increaseTypes) && $operationType != HistoryInterface::TYPE_ALLOCATED) ||
            ($operationType == HistoryInterface::TYPE_ALLOCATED && $this->checkHistoryExist($balanceId))
        ) {
            throw new InputException(__('Cannot process the request. Please check the operation type and try again.'));
        }

        if ($value < 0) {
            throw new InputException(
                __(
                    'Invalid attribute value. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'value', 'fieldValue' => $value]
                )
            );
        }
        if (!$balanceId) {
            throw new InputException(
                __(
                    '"%fieldName" is required. Enter and try again.',
                    ['fieldName' => 'balanceId']
                )
            );
        }
        if (!$this->websiteCurrency->isCreditCurrencyEnabled($currency)) {
            throw new InputException(
                __(
                    'Invalid attribute value. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'currency', 'fieldValue' => $currency]
                )
            );
        }
    }

    /**
     * Check history of Company Credit exist.
     *
     * @param int $balanceId
     * @return bool
     */
    private function checkHistoryExist($balanceId)
    {
        /** @var \Magento\CompanyCredit\Model\ResourceModel\History\Collection $collection */
        $collection = $this->historyCollectionFactory->create();
        $collection->addFieldToFilter('company_credit_id', ['eq' => $balanceId]);
        return (bool)$collection->getSize();
    }
}
