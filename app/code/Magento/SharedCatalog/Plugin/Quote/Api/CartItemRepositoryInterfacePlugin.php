<?php
namespace Magento\SharedCatalog\Plugin\Quote\Api;

use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessorFactory;

/**
 * Class for applying custom options on quote items.
 */
class CartItemRepositoryInterfacePlugin
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartItemOptionsProcessorFactory
     */
    private $cartItemOptionsProcessorFactory;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param CartItemOptionsProcessorFactory $cartItemOptionsProcessorFactory
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CartItemOptionsProcessorFactory $cartItemOptionsProcessorFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartItemOptionsProcessorFactory = $cartItemOptionsProcessorFactory;
    }

    /**
     * To be removed when original method is fixed.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param \Closure $method
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\CartItemInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetList(
        \Magento\Quote\Api\CartItemRepositoryInterface $subject,
        \Closure $method,
        $cartId
    ) {
        $output = [];
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId, ['*']);

        $processor = $this->cartItemOptionsProcessorFactory->create();
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $item = $processor->addProductOptions($item->getProductType(), $item);
            $output[] = $processor->applyCustomOptions($item);
        }
        return $output;
    }
}
