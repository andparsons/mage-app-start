<?php
namespace Magento\Eway\Gateway\Command;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;

/**
 * Class CaptureStrategyCommand
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**
     * eWAY Direct sale command
     */
    const SALE = 'sale';

    /**
     * eWAY Direct capture command
     */
    const PRE_AUTH_CAPTURE = 'pre_auth_capture';

    /**
     * @var Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @param Command\CommandPoolInterface $commandPool
     */
    public function __construct(
        Command\CommandPoolInterface $commandPool
    ) {
        $this->commandPool = $commandPool;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return Command\ResultInterface|null
     * @throws Command\CommandException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Order\Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        if ($paymentInfo instanceof Order\Payment
            && $paymentInfo->getAuthorizationTransaction()
        ) {
            return $this->commandPool
                ->get(self::PRE_AUTH_CAPTURE)
                ->execute($commandSubject);
        }

        return $this->commandPool
            ->get(self::SALE)
            ->execute($commandSubject);
    }
}
