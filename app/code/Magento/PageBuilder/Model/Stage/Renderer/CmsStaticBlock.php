<?php

declare(strict_types=1);

namespace Magento\PageBuilder\Model\Stage\Renderer;

use Psr\Log\LoggerInterface;

/**
 * Renders a CMS Block for the stage
 *
 * @api
 */
class CmsStaticBlock implements \Magento\PageBuilder\Model\Stage\RendererInterface
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var WidgetDirective
     */
    private $widgetDirectiveRenderer;

    /**
     * @var LoggerInterface
     */
    private $loggerInterface;

    /**
     * @var \Magento\PageBuilder\Model\Stage\HtmlFilter
     */
    private $htmlFilter;

    /**
     * CmsStaticBlock constructor.
     *
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     * @param WidgetDirective $widgetDirectiveRenderer
     * @param LoggerInterface $loggerInterface
     * @param \Magento\PageBuilder\Model\Stage\HtmlFilter $htmlFilter
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        WidgetDirective $widgetDirectiveRenderer,
        LoggerInterface $loggerInterface,
        \Magento\PageBuilder\Model\Stage\HtmlFilter $htmlFilter
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->widgetDirectiveRenderer = $widgetDirectiveRenderer;
        $this->loggerInterface = $loggerInterface;
        $this->htmlFilter = $htmlFilter;
    }

    /**
     * Render a state object for the specified block for the stage preview
     *
     * @param array $params
     * @return array
     */
    public function render(array $params): array
    {
        $result = [
            'title' => null,
            'content' => null,
            'error' => null
        ];

        // Short-circuit if needed fields aren't present
        if (empty($params['directive']) && empty($params['block_id'])) {
            return $result;
        }

        $collection = $this->blockCollectionFactory->create();
        $blocks = $collection
            ->addFieldToSelect(['title', 'is_active'])
            ->addFieldToFilter('block_id', ['eq' => $params['block_id']])
            ->load();

        if ($blocks->count() === 0) {
            $result['error'] = sprintf(__('Block with ID: %s doesn\'t exist'), $params['block_id']);

            return $result;
        }

        /**
         * @var \Magento\Cms\Model\Block $block
         */
        $block = $blocks->getFirstItem();
        $result['title'] = $block->getTitle();

        if ($block->isActive()) {
            $directiveResult = $this->widgetDirectiveRenderer->render($params);
            $result['content'] = $this->htmlFilter->filterHtml($directiveResult['content']);
        } else {
            $result['error'] = __('Block disabled');
        }

        return $result;
    }
}
