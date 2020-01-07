<?php
declare(strict_types=1);

namespace Magento\RequisitionList\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * Product options view model for requisition list item.
 */
class Downloadable implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequisitionListItemInterface
     */
    private $item;

    /**
     * @param Json $serializer
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Json $serializer,
        Escaper $escaper,
        UrlInterface $urlBuilder
    ) {
        $this->serializer = $serializer;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Set requisition list item.
     *
     * @param RequisitionListItemInterface $item
     * @return $this
     */
    public function setItem(RequisitionListItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get requisition list item.
     *
     * @return RequisitionListItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Get option value for file.
     *
     * @param array $option
     * @return string
     */
    public function getOptionValueForFile(array $option): string
    {
        $itemOption = $this->serializer->unserialize(
            $this->getItem()->getOptions()[AbstractType::OPTION_PREFIX . $option['option_id']]
        );

        $sizes = $this->prepareSize($itemOption);

        return sprintf(
            '<a href="%s" target="_blank">%s</a> %s',
            $this->getOptionDownloadUrl(
                'requisition_list/download/downloadCustomOption',
                $this->getUrlParams($option, $itemOption)
            ),
            $this->escaper->escapeHtml($itemOption['title']),
            $sizes
        );
    }

    /**
     * Get option download url.
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getOptionDownloadUrl(string $route, array $params)
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Get params for url.
     *
     * @param array $option
     * @param array $itemOption
     * @return array
     */
    private function getUrlParams(array $option, array $itemOption)
    {
        return [
            'id' => $this->getItem()->getId(),
            'code' => AbstractType::OPTION_PREFIX . $option['option_id'],
            'key' => $itemOption['secret_key']
        ];
    }

    /**
     * Prepare filesize.
     *
     * @param array $option
     * @return string
     */
    private function prepareSize($option)
    {
        $sizes = '';
        if (!empty($option['width']) && !empty($option['height']) && $option['width'] > 0 && $option['height'] > 0) {
            $sizes = $option['width'] . ' x ' . $option['height'] . ' ' . __('px.');
        }
        return $sizes;
    }
}
