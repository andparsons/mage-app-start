<?php

namespace Magento\CompanyCredit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Logging of the credit data updates in the history.
 */
class CreditLimitHistory
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryFactory
     */
    private $historyFactory;

    /**
     * @var \Magento\CompanyCredit\Model\Creator
     */
    private $creator;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryHydrator
     */
    private $historyHydrator;

    /**
     * @param HistoryRepositoryInterface $historyRepository
     * @param UserContextInterface $userContext
     * @param CompanyRepositoryInterface $companyRepository
     * @param Creator $creator
     * @param HistoryFactory $historyFactory
     * @param HistoryHydrator $historyHydrator
     */
    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        UserContextInterface $userContext,
        CompanyRepositoryInterface $companyRepository,
        Creator $creator,
        HistoryFactory $historyFactory,
        HistoryHydrator $historyHydrator
    ) {
        $this->historyRepository = $historyRepository;
        $this->userContext = $userContext;
        $this->companyRepository = $companyRepository;
        $this->creator = $creator;
        $this->historyFactory = $historyFactory;
        $this->historyHydrator = $historyHydrator;
    }

    /**
     * Create new history log entity with credit limit.
     *
     * @param CreditLimitInterface $creditLimit
     * @param int $type
     * @param float $amount
     * @param string $currency [optional]
     * @param string $comment [optional]
     * @param array $systemComments [optional]
     * @param \Magento\Framework\DataObject|null $options [optional]
     * @return void
     * @throws CouldNotSaveException
     */
    public function logCredit(
        CreditLimitInterface $creditLimit,
        $type,
        $amount,
        $currency = '',
        $comment = '',
        array $systemComments = [],
        \Magento\Framework\DataObject $options = null
    ) {

        /** @var HistoryInterface $history */
        $history = $this->historyHydrator->hydrate(
            $this->historyFactory->create(),
            $creditLimit,
            $type,
            $amount,
            $currency,
            $comment,
            $systemComments,
            $options
        );
        $this->historyRepository->save($history);
    }

    /**
     * Log changes of credit data to history.
     *
     * @param CreditLimitInterface $credit
     * @param CreditLimitInterface $originCredit
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function logUpdateItem(CreditLimitInterface $credit, CreditLimitInterface $originCredit)
    {
        if (!$originCredit->getId()) {
            return;
        }

        $hasChanges = false;
        $systemComments = [];

        if ($credit->getCurrencyCode() != $originCredit->getCurrencyCode()) {
            $changeCurrencyComment = $this->prepareChangeCurrencyComment(
                $originCredit->getCurrencyCode(),
                $credit->getCurrencyCode(),
                $credit->getCurrencyRate()
            );
            $systemComments[HistoryInterface::COMMENT_TYPE_UPDATE_CURRENCY] = $changeCurrencyComment;
            $hasChanges = true;
        }

        if ($credit->getCreditLimit() != $originCredit->getCreditLimit()) {
            $hasChanges = true;
        }

        if ($credit->getExceedLimit() != $originCredit->getExceedLimit()) {
            $changeExceedLimitComment = $this->prepareChangeExceedLimitComment($credit);
            $systemComments[HistoryInterface::COMMENT_TYPE_UPDATE_EXCEED_LIMIT] = $changeExceedLimitComment;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->logCredit(
                $credit,
                HistoryInterface::TYPE_UPDATED,
                0,
                '',
                $credit->getCreditComment(),
                $systemComments
            );
        }
    }

    /**
     * Prepare data for system comment with change currency type.
     *
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param string $currencyRate
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareChangeCurrencyComment($currencyFrom, $currencyTo, $currencyRate)
    {
        $userId = $this->userContext->getUserId();
        $userName = $this->creator->retrieveCreatorName(UserContextInterface::USER_TYPE_ADMIN, $userId);

        return [
            'currency_from' => $currencyFrom,
            'currency_to' => $currencyTo,
            'currency_rate' => number_format($currencyRate, 4),
            'user_name' => $userName,
        ];
    }

    /**
     * Prepare data for system comment with change exceed limit type.
     *
     * @param CreditLimitInterface $creditLimit
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareChangeExceedLimitComment(CreditLimitInterface $creditLimit)
    {
        $company = $this->companyRepository->get($creditLimit->getCompanyId());

        return [
            'value' => $creditLimit->getExceedLimit(),
            'company_name' => $company->getCompanyName(),
            'user_name' => $this->creator->retrieveCreatorName(
                $this->userContext->getUserType(),
                $this->userContext->getUserId()
            )
        ];
    }

    /**
     * Log credit limit updates.
     *
     * @param CreditLimitInterface $creditLimit
     * @param string $comment
     * @param array $systemComments
     * @return void
     * @throws CouldNotSaveException
     */
    public function logUpdateCreditLimit(CreditLimitInterface $creditLimit, $comment, array $systemComments)
    {
        $this->logCredit($creditLimit, HistoryInterface::TYPE_UPDATED, 0, '', $comment, $systemComments);
    }
}
