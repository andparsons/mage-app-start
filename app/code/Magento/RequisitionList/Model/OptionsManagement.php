<?php
namespace Magento\RequisitionList\Model;

use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\Serialize\JsonValidator;

/**
 * Actions with requisition list item options.
 */
class OptionsManagement
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\OptionFactory
     */
    private $itemOptionsFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items
     */
    private $requisitionListItemRepository;

    /**
     * @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var JsonValidator
     */
    private $jsonValidator;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $requisitionListItems = [];

    /**
     * @var string
     */
    private $simpleProductOptionCode = 'simple_product';

    /**
     * Constructor
     *
     * @param RequisitionListItem\OptionFactory $itemOptionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param RequisitionList\Items $requisitionListItemRepository
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory $requisitionListItemFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param JsonValidator $jsonValidator
     */
    public function __construct(
        \Magento\RequisitionList\Model\RequisitionListItem\OptionFactory $itemOptionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\RequisitionList\Model\RequisitionList\Items $requisitionListItemRepository,
        \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory $requisitionListItemFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        JsonValidator $jsonValidator
    ) {
        $this->itemOptionsFactory = $itemOptionFactory;
        $this->productRepository = $productRepository;
        $this->requisitionListItemRepository = $requisitionListItemRepository;
        $this->requisitionListItemFactory = $requisitionListItemFactory;
        $this->serializer = $serializer;
        $this->jsonValidator = $jsonValidator;
    }

    /**
     * Prepare requisition list item options.
     *
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemInterface $item
     * @param \Magento\Catalog\Api\Data\ProductInterface $product [optional]
     * @return array
     */
    public function getOptions(
        \Magento\RequisitionList\Api\Data\RequisitionListItemInterface $item,
        \Magento\Catalog\Api\Data\ProductInterface $product = null
    ) {
        $optionId = $item->getId() ?: 0;
        if (!isset($this->options[$optionId])) {
            $options = $item->getOptions();
            if (is_string($options) && $this->jsonValidator->isValid($options)) {
                $options = $this->serializer->unserialize($options);
            }

            $this->options[$optionId] = [];
            if (is_array($options)) {
                foreach ($options as $optionCode => $optionData) {
                    $this->options[$optionId][$optionCode] = $this->prepareOption($optionData, $optionCode, $product);
                }
            }
        }

        return $this->options[$optionId];
    }

    /**
     * Drop options cache.
     *
     * @param int $itemId
     * @return void
     */
    public function clearOptions($itemId)
    {
        unset($this->options[$itemId]);
    }

    /**
     * Prepare requisition list item options, locate requisition list item by item id.
     *
     * @param int $itemId
     * @param \Magento\Catalog\Api\Data\ProductInterface|null $product [optional]
     * @return array
     */
    public function getOptionsByRequisitionListItemId(
        $itemId,
        \Magento\Catalog\Api\Data\ProductInterface $product = null
    ) {
        $item = $this->getRequisitionListItem($itemId);
        return $this->getOptions($item, $product);
    }

    /**
     * Add option to list.
     *
     * @param OptionInterface|\Magento\Framework\DataObject|array $option
     * @param int|null $itemId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addOption($option, $itemId)
    {
        $itemId = $itemId ?: 0;

        if (is_array($option)) {
            $option = $this->itemOptionsFactory->create()->setData($option);
        } elseif ($option instanceof \Magento\Framework\DataObject) {
            $option = $this->itemOptionsFactory->create()->setData($option->getData())
                ->setProduct($option->getProduct());
        } elseif (!($option instanceof \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid item option format.'));
        }

        $this->addOptionByCode($option, $option->getCode(), $itemId);
    }

    /**
     * Get info_buyRequest option data.
     *
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemInterface $item
     * @return array
     */
    public function getInfoBuyRequest(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface $item)
    {
        $options = $item->getOptions();
        $options = is_array($options) ? $options : $this->serializer->unserialize($options);
        $infoBuyRequest = (!empty($options['info_buyRequest'])) ? $options['info_buyRequest'] : [];
        $infoBuyRequest = is_array($infoBuyRequest)
            ? $infoBuyRequest : $this->serializer->unserialize($infoBuyRequest);

        return $infoBuyRequest;
    }

    /**
     * Populate option.
     *
     * @param array|string $optionData
     * @param string $optionCode
     * @param \Magento\Catalog\Api\Data\ProductInterface|null $product [optional]
     * @return \Magento\RequisitionList\Model\RequisitionListItem\Option
     */
    private function prepareOption(
        $optionData,
        $optionCode,
        \Magento\Catalog\Api\Data\ProductInterface $product = null
    ) {
        $option = $this->itemOptionsFactory->create()
            ->setData('value', $optionData);
        $option->setData('code', $optionCode);

        if ($product !== null) {
            $option->setProduct($product);
        }

        if ($optionCode === $this->simpleProductOptionCode) {
            $simpleProduct = $this->productRepository->getById($optionData);
            $option->setProduct($simpleProduct);
        }

        return $option;
    }

    /**
     * Add option to the options list.
     *
     * @param OptionInterface $option
     * @param string $code
     * @param int $optionId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addOptionByCode(
        \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface $option,
        $code,
        $optionId
    ) {
        if (!isset($this->options[$optionId][$code])) {
            $this->options[$optionId][$code] = $option;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An item option with code %1 already exists.', $code)
            );
        }
    }

    /**
     * Get requisition list item by id.
     *
     * @param int $itemId
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemInterface
     */
    private function getRequisitionListItem($itemId)
    {
        if (!isset($this->requisitionListItems[$itemId])) {
            if ($itemId) {
                $this->requisitionListItems[$itemId] = $this->requisitionListItemRepository->get($itemId);
            } else {
                $this->requisitionListItems[$itemId] = $this->requisitionListItemFactory->create();
            }
        }

        return $this->requisitionListItems[$itemId];
    }
}
