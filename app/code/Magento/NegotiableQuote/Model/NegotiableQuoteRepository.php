<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\NegotiableQuote\Model\Query\GetList;
use Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\InputException;

/**
 * Negotiable quote repository object.
 */
class NegotiableQuoteRepository implements NegotiableQuoteRepositoryInterface
{
    /**
     * Negotiable quote factory.
     *
     * @var NegotiableQuoteInterfaceFactory
     */
    private $negotiableQuoteFactory;

    /**
     * Negotiable quote resource model.
     *
     * @var NegotiableQuoteResource
     */
    private $negotiableQuoteResource;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var GetList
     */
    private $negotiableQuoteList;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory
     */
    private $validatorFactory;

    /**
     * @param NegotiableQuoteInterfaceFactory $negotiableQuoteFactory
     * @param NegotiableQuoteResource $negotiableQuoteResource
     * @param UserContextInterface $userContext
     * @param GetList $negotiableQuoteList
     * @param ValidatorInterfaceFactory $validatorFactory
     */
    public function __construct(
        NegotiableQuoteInterfaceFactory $negotiableQuoteFactory,
        NegotiableQuoteResource $negotiableQuoteResource,
        UserContextInterface $userContext,
        GetList $negotiableQuoteList,
        ValidatorInterfaceFactory $validatorFactory
    ) {
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        $this->negotiableQuoteResource = $negotiableQuoteResource;
        $this->userContext = $userContext;
        $this->negotiableQuoteList = $negotiableQuoteList;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $snapshots = false)
    {
        return $this->negotiableQuoteList->getList($searchCriteria, $snapshots);
    }

    /**
     * {@inheritdoc}
     */
    public function getListByCustomerId($customerId)
    {
        return $this->negotiableQuoteList->getListByCustomerId($customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($quoteId)
    {
        $negotiableQuote = $this->negotiableQuoteFactory->create()->load($quoteId);

        return $negotiableQuote;
    }

    /**
     * {@inheritdoc}
     */
    public function save(NegotiableQuoteInterface $negotiableQuote)
    {
        if (!$negotiableQuote->getQuoteId()) {
            return false;
        }
        $this->initNegotiableQuoteParameters($negotiableQuote);

        $validator = $this->validatorFactory->create(['action' => 'save']);
        $validateResult = $validator->validate(['negotiableQuote' => $negotiableQuote]);
        if ($validateResult->hasMessages()) {
            $exception = new InputException();
            foreach ($validateResult->getMessages() as $message) {
                $exception->addError($message);
            }
            throw $exception;
        }

        try {
            $this->negotiableQuoteResource->saveNegotiatedQuoteData($negotiableQuote);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Changes to the negotiated quote were not saved. Please try again.'));
        }

        return true;
    }

    /**
     * Set default parameters (price, creator) for negotiable quote.
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return void
     */
    private function initNegotiableQuoteParameters(NegotiableQuoteInterface $negotiableQuote)
    {
        $oldQuote = $this->negotiableQuoteFactory->create();
        $this->negotiableQuoteResource->load($oldQuote, $negotiableQuote->getQuoteId());

        $value = $negotiableQuote->hasData(NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE)
            ? $negotiableQuote->getNegotiatedPriceValue()
            : $oldQuote->getNegotiatedPriceValue();
        if ($value === null) {
            $negotiableQuote->setNegotiatedPriceType(null);
        }
        if ($negotiableQuote->getCreatorId() === null && $oldQuote->getCreatorId() === null) {
            $negotiableQuote->setCreatorId($this->userContext->getUserId());
            $negotiableQuote->setCreatorType($this->userContext->getUserType());
        }

        if ($oldQuote->getQuoteId() && $this->isStatusCanSetProcessingByAdmin($negotiableQuote, $oldQuote)) {
            $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        }
    }

    /**
     * Check that status of Negotiable Quote can be set to "Processing by admin".
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param NegotiableQuoteInterface $oldNegotiableQuote
     * @return bool
     */
    private function isStatusCanSetProcessingByAdmin(
        NegotiableQuoteInterface $negotiableQuote,
        NegotiableQuoteInterface $oldNegotiableQuote
    ) {
        $adminUserContext = [
            UserContextInterface::USER_TYPE_ADMIN,
            UserContextInterface::USER_TYPE_INTEGRATION
        ];
        $statusesForProcessing = [
            NegotiableQuoteInterface::STATUS_CREATED,
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER
        ];
        $status = $negotiableQuote->getStatus() ?: $oldNegotiableQuote->getStatus();

        return in_array($this->userContext->getUserType(), $adminUserContext)
            && in_array($status, $statusesForProcessing)
            && $this->quoteHasChanges($negotiableQuote, $oldNegotiableQuote);
    }

    /**
     * Check whether a negotiable quote contains changed fields.
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param NegotiableQuoteInterface $oldQuote
     * @return bool
     */
    private function quoteHasChanges(NegotiableQuoteInterface $negotiableQuote, NegotiableQuoteInterface $oldQuote)
    {
        $fieldsCheck = [
            NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE,
            NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE,
            NegotiableQuoteInterface::SHIPPING_PRICE,
            NegotiableQuoteInterface::EXPIRATION_PERIOD
        ];
        foreach ($fieldsCheck as $field) {
            if ($negotiableQuote->hasData($field) && $negotiableQuote->getData($field) != $oldQuote->getData($field)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(NegotiableQuoteInterface $quote)
    {
        try {
            $this->negotiableQuoteResource->delete($quote);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete negotiable quote'));
        }
    }
}
