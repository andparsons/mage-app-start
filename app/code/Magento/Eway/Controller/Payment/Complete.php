<?php
namespace Magento\Eway\Controller\Payment;

use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Sales\Api\PaymentFailuresInterface;

/**
 * Class Complete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class Complete extends Action
{
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var PaymentFailuresInterface
     */
    private $paymentFailures;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     * @param LayoutFactory $layoutFactory
     * @param Session $checkoutSession
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param SessionManager $sessionManager
     * @param PaymentFailuresInterface|null $paymentFailures
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        LoggerInterface $logger,
        LayoutFactory $layoutFactory,
        Session $checkoutSession,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        SessionManager $sessionManager,
        PaymentFailuresInterface $paymentFailures = null
    ) {
        parent::__construct($context);

        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->layoutFactory = $layoutFactory;
        $this->checkoutSession = $checkoutSession;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->sessionManager = $sessionManager;
        $this->paymentFailures = $paymentFailures ?: $this->_objectManager->get(PaymentFailuresInterface::class);
    }

    /**
     * Execute action based on request and return result.
     *
     * @return ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultLayout = $this->layoutFactory->create();
        $resultLayout->addDefaultHandle();
        $processor = $resultLayout->getLayout()->getUpdate();
        $order = $this->checkoutSession->getLastRealOrder();

        try {
            $payment = $order->getPayment();

            $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
            $arguments['request'] = $this->getRequest()->getParams();
            $arguments['access_code'] = $this->sessionManager->getAccessCode();

            $this->commandPool->get('complete')->execute($arguments);

            $processor->load(['response_success']);
        } catch (\Exception $e) {
            $this->paymentFailures->handle((int)$order->getQuoteId(), $e->getMessage());
            $this->logger->critical($e);
            $processor->load(['response_failure']);
        }

        return $resultLayout;
    }
}
