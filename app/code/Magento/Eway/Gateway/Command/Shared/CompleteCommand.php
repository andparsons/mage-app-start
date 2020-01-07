<?php
namespace Magento\Eway\Gateway\Command\Shared;

use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CompleteCommand
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CompleteCommand implements CommandInterface
{
    /**
     * @var UpdateDetailsCommand
     */
    private $updateDetailsCommand;

    /**
     * @var UpdateOrderCommand
     */
    private $updateOrderCommand;

    /**
     * @param UpdateDetailsCommand $updateDetailsCommand
     * @param UpdateOrderCommand $updateOrderCommand
     */
    public function __construct(
        UpdateDetailsCommand $updateDetailsCommand,
        UpdateOrderCommand $updateOrderCommand
    ) {
        $this->updateDetailsCommand = $updateDetailsCommand;
        $this->updateOrderCommand = $updateOrderCommand;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return Command\ResultInterface|void|null
     * @throws Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        $this->updateDetailsCommand->execute($commandSubject);
        $this->updateOrderCommand->execute($commandSubject);
    }
}
