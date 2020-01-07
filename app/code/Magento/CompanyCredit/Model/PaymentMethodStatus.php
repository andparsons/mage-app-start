<?php
namespace Magento\CompanyCredit\Model;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PaymentMethodStatus.
 */
class PaymentMethodStatus
{
    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    private $methodSpecificationFactory;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * Company check.
     *
     * @var string
     */
    private $companyCheck = 'company';

    /**
     * Company credit method code.
     *
     * @var string
     */
    private $companyCreditMethodCode = 'companycredit';

    /**
     * Link constructor.
     *
     * @param \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList
     * @param \Magento\Store\Api\StoreResolverInterface $storeResolver
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Payment\Model\Method\InstanceFactory $paymentMethodInstanceFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param StoreManagerInterface|null $storeManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \Magento\Store\Api\StoreResolverInterface $storeResolver,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Payment\Model\Method\InstanceFactory $paymentMethodInstanceFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager = null
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->storeManager = $storeManager;
        $this->userContext = $userContext;
        $this->quoteRepository = $quoteRepository;
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->paymentMethodInstanceFactory = $paymentMethodInstanceFactory;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * Checks if company credit link is enabled.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEnabled()
    {
        $isEnabled = false;
        $paymentMethods = $this->paymentMethodList->getActiveList($this->storeManager->getStore()->getId());

        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->getCode() === $this->companyCreditMethodCode) {
                try {
                    $activeQuote = $this->quoteRepository->getActiveForCustomer($this->userContext->getUserId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $activeQuote = $this->quoteFactory->create()->setCustomerId($this->userContext->getUserId());
                }

                $paymentMethodInstance = $this->paymentMethodInstanceFactory->create($paymentMethod);
                $isEnabled = $this->methodSpecificationFactory->create([$this->companyCheck])
                    ->isApplicable($paymentMethodInstance, $activeQuote);
                break;
            }
        }

        return $isEnabled;
    }
}
