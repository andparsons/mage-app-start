<?php

declare(strict_types=1);

namespace Magento\RequisitionList\Model\RequisitionListItem\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\RequisitionList\Model\RequisitionListItem\Options\Builder\ConfigurationException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\RequisitionList\Model\RequisitionListItem\OptionFactory;
use Magento\RequisitionList\Model\OptionsManagement;
use Magento\RequisitionList\Model\RequisitionListItem\Locator as RequisitionListItemLocator;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Requisition List Item options builder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Builder
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var OptionsManagement
     */
    private $optionsManagement;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var RequisitionListItemLocator
     */
    private $requisitionListItemLocator;

    /**
     * @var string
     */
    private $infoBuyRequestOptionCode = 'info_buyRequest';

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param OptionFactory $optionFactory
     * @param OptionsManagement $optionsManagement
     * @param SerializerInterface $serializer
     * @param ProductHelper|null $productHelper
     * @param RequisitionListItemLocator|null $requisitionListItemLocator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        OptionFactory $optionFactory,
        OptionsManagement $optionsManagement,
        SerializerInterface $serializer,
        ProductHelper $productHelper = null,
        RequisitionListItemLocator $requisitionListItemLocator = null
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->optionFactory = $optionFactory;
        $this->optionsManagement = $optionsManagement;
        $this->serializer = $serializer;
        $this->productHelper = $productHelper ?: ObjectManager::getInstance()->get(
            ProductHelper::class
        );
        $this->requisitionListItemLocator = $requisitionListItemLocator ?: ObjectManager::getInstance()->get(
            RequisitionListItemLocator::class
        );
    }

    /**
     * Prepare options for the requisition list item.
     *
     * @param array $buyRequest
     * @param int $itemId
     * @param bool $allowMisconfiguredProducts
     * @return array
     * @throws LocalizedException
     * @throws ConfigurationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(array $buyRequest, $itemId, $allowMisconfiguredProducts)
    {
        $itemOptions = ['info_buyRequest' => $buyRequest];

        if (isset($buyRequest['product'])) {
            $productId = $buyRequest['product'];
        }

        if (!isset($productId)) {
            return $itemOptions;
        }

        $storeId = $this->storeManager->getStore()->getId();
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Cannot specify product.'));
        }

        $params = [];
        if ((int)$itemId) {
            $item = $this->requisitionListItemLocator->getItem($itemId);
            $params['current_config'] = $this->optionsManagement->getInfoBuyRequest($item);
        }
        $buyRequest = $this->productHelper->addParamsToBuyRequest($buyRequest, $params);

        $cartCandidates = $product->getTypeInstance()->processConfiguration(
            $buyRequest,
            clone $product,
            AbstractType::PROCESS_MODE_FULL
        );

        if (is_string($cartCandidates) || $cartCandidates instanceof Phrase) {
            if ($allowMisconfiguredProducts) {
                return [];
            }
            throw new ConfigurationException(__($cartCandidates));
        }

        $cartCandidates = (array)$cartCandidates;
        $parentProduct = null;
        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $parentProduct = $candidate;
        }

        $options = $this->retrieveItemOptions($itemId, $parentProduct);

        return $options;
    }

    /**
     * Retrieve requisition item options.
     *
     * @param int $itemId
     * @param ProductInterface $product
     * @return array
     * @throws LocalizedException
     */
    private function retrieveItemOptions($itemId, ProductInterface $product)
    {
        $this->optionsManagement->clearOptions($itemId);

        $productOptions = $product->getCustomOptions();
        foreach ($productOptions as $productOption) {
            $this->optionsManagement->addOption($productOption, $itemId);
        }

        $itemOptions = $this->optionsManagement->getOptionsByRequisitionListItemId($itemId);
        $options = [];

        foreach ($itemOptions as $code => $option) {
            $options[$code] = $option->getValue();
            if ($code === $this->infoBuyRequestOptionCode && is_string($option->getValue())) {
                $options[$code] = $this->serializer->unserialize($option->getValue());
            }
        }

        return $options;
    }
}
