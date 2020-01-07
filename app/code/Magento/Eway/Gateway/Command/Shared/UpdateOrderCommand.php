<?php
namespace Magento\Eway\Gateway\Command\Shared;

use Magento\Payment\Gateway\Command;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class UpdateOrderCommand
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class UpdateOrderCommand implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return Command\ResultInterface|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        switch ($this->config->getValue('payment_action')) {
            case AbstractMethod::ACTION_AUTHORIZE_CAPTURE:
                $payment->capture();
                break;
            case AbstractMethod::ACTION_AUTHORIZE:
                $payment->authorize(
                    false,
                    $paymentDO->getOrder()->getGrandTotalAmount()
                );
                break;
        }

        $this->orderRepository->save($payment->getOrder());
    }
}
