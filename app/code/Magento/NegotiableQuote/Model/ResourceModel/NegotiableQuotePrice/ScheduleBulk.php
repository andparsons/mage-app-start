<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuotePrice;

use Magento\Framework\Bulk\BulkManagementInterface as BulkManagement;
use Magento\Framework\DataObject\IdentityGeneratorInterface as IdentityGenerator;
use Magento\NegotiableQuote\Model\OperationBuilder;
use Magento\AsynchronousOperations\Api\Data\OperationInterface as BulkOperation;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;
use Magento\Framework\Exception\LocalizedException;

/**
 * Schedule bulk to process of Negotiable quotes.
 */
class ScheduleBulk
{
    const OPERATION_DATA_QUOTE_ID = 'quote_id';

    /**
     * @var BulkManagement
     */
    private $bulkManagement;

    /**
     * @var IdentityGenerator
     */
    private $identityGenerator;

    /**
     * @var OperationBuilder
     */
    private $operationBuilder;

    /**
     * @var string
     */
    private $queueTopic = 'negotiable.quote.price.updated';

    /**
     * @param BulkManagement $bulkManagement
     * @param IdentityGenerator $identityGenerator
     * @param OperationBuilder $operationBuilder
     */
    public function __construct(
        BulkManagement $bulkManagement,
        IdentityGenerator $identityGenerator,
        OperationBuilder $operationBuilder
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->identityGenerator = $identityGenerator;
        $this->operationBuilder = $operationBuilder;
    }

    /**
     * Schedule bulk.
     *
     * @param NegotiableQuote[] $negotiableQuotes
     * @param int $userId
     * @throws LocalizedException
     */
    public function execute(array $negotiableQuotes, int $userId): void
    {
        $operationCount = count($negotiableQuotes);

        if ($operationCount == 0) {
            return;
        }

        $bulkUuid = $this->identityGenerator->generateId();
        $operations = $this->getBulkOperations($negotiableQuotes, $bulkUuid);
        $bulkDescription = __('Negotiable quotes update');
        $result = $this->bulkManagement->scheduleBulk($bulkUuid, $operations, $bulkDescription, $userId);
        
        if (!$result) {
            throw new LocalizedException(__('Something went wrong while scheduling operations.'));
        }
    }

    /**
     * Return bulk operations.
     *
     * @param NegotiableQuote[] $negotiableQuotes
     * @param string $bulkUuid
     * @return BulkOperation[]
     */
    private function getBulkOperations(array $negotiableQuotes, string $bulkUuid): array
    {
        $operations = [];
        foreach ($negotiableQuotes as $quote) {
            $operationData = [
                self::OPERATION_DATA_QUOTE_ID => $quote->getId(),
            ];
            $operations[] = $this->operationBuilder->build($bulkUuid, $this->queueTopic, $operationData);
        }
        return $operations;
    }
}
