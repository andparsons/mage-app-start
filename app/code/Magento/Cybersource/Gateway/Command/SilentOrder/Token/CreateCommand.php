<?php
namespace Magento\Cybersource\Gateway\Command\SilentOrder\Token;

use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Logger;

/**
 * Command generates data for token creating.
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CreateCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param BuilderInterface $builder
     * @param ArrayResultFactory $arrayResultFactory
     * @param Logger $logger
     */
    public function __construct(
        BuilderInterface $builder,
        ArrayResultFactory $arrayResultFactory,
        Logger $logger
    ) {
        $this->builder = $builder;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->logger = $logger;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return ArrayResult
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $result = $this->builder->build($commandSubject);
        // sending store id and other additional keys are restricted by Cybersource API
        unset($result['store_id']);

        $this->logger->debug(['payment_token_request' => $result]);

        return $this->arrayResultFactory->create(['array' => $result]);
    }
}
