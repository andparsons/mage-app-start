<?php

namespace Magento\CompanyCredit\Ui\Component\History\Listing\Column;

use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;
use Magento\CompanyCredit\Model\Sales\OrderLocator;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class for rendering company credit history comments on UI listing.
 */
class Comment extends Column
{
    /**
     * @var \Magento\CompanyCredit\Model\Sales\OrderLocator
     */
    private $orderLocator;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderLocator $orderLocator
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param SerializerInterface $serializer
     * @param array $components [optional]
     * @param array $data [optional]
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderLocator $orderLocator,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        SerializerInterface $serializer,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderLocator = $orderLocator;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->getCommentLabel(
                        $item[$fieldName],
                        $item[HistoryInterface::TYPE]
                    );
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get label by serialized comment data.
     *
     * @param string $serialized
     * @param int $operationType
     * @return string
     */
    private function getCommentLabel($serialized, $operationType)
    {
        $comment = '';
        if (!empty($serialized)) {
            $commentUnserialize = $this->serializer->unserialize($serialized);
            $commentArray = [];
            if (!empty($commentUnserialize['system'])) {
                $commentArray += $this->renderSystemComments($commentUnserialize['system'], $operationType);
            }
            if (!empty($commentUnserialize['custom'])) {
                $commentArray[] = $this->escaper->escapeHtml($commentUnserialize['custom']);
            }
            $comment = implode('<br />', $commentArray);
        }
        return $comment;
    }

    /**
     * Render system comments from data array.
     *
     * @param array $comments
     * @param int $operationType
     * @return string[]
     */
    private function renderSystemComments(array $comments, $operationType)
    {
        // filter necessary comments
        $comments = array_filter(
            $comments,
            function ($commentType) use ($operationType) {
                $isRefundComment = $commentType === HistoryInterface::COMMENT_TYPE_ORDER
                    && $operationType == HistoryInterface::TYPE_REFUNDED;
                return !$isRefundComment;
            },
            ARRAY_FILTER_USE_KEY
        );

        $renderedComments = [];

        foreach ($comments as $type => $commentData) {
            $renderedComments[] = $this->renderSystemComment($type, $commentData);
        }

        return $renderedComments;
    }

    /**
     * Render system comments with type-specific strategy.
     *
     * @param string $type
     * @param mixed $data
     * @return null|string
     */
    private function renderSystemComment($type, $data)
    {
        $comment = null;
        switch ($type) {
            case HistoryInterface::COMMENT_TYPE_UPDATE_EXCEED_LIMIT:
                $comment = $this->renderExceedLimitComment($data);
                break;
            case HistoryInterface::COMMENT_TYPE_UPDATE_CURRENCY:
                $comment = $this->renderChangeCurrencyComment($data);
                break;
            case HistoryInterface::COMMENT_TYPE_ORDER:
                $comment = $this->renderOrderComment($data);
                break;
        }

        return $comment;
    }

    /**
     * Render exceed limit comment message.
     *
     * @param array $commentData
     * @return string
     */
    private function renderExceedLimitComment(array $commentData)
    {
        if ($commentData['value']) {
            $commentsString = __(
                '%1 made an update. %2 can exceed the Credit Limit.',
                $commentData['user_name'],
                $commentData['company_name']
            );
        } else {
            $commentsString = __(
                '%1 made an update. %2 cannot exceed the Credit Limit.',
                $commentData['user_name'],
                $commentData['company_name']
            );
        }
        return $commentsString;
    }

    /**
     * Render order comment message.
     *
     * @param int $orderId
     * @return string
     */
    private function renderOrderComment($orderId)
    {
        try {
            $order = $this->orderLocator->getOrderByIncrementId($orderId);
            $url = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getEntityId()]);
            $link = '<a href="' . $url . '"">' . $order->getIncrementId() . '</a>';
            $commentsString = __('Order # %1', $link);
        } catch (NoSuchEntityException $e) {
            // If order not found then don't render comment.
            $commentsString = '';
        }
        return $commentsString;
    }

    /**
     * Comment renderer for change currency type.
     *
     * @param array $data
     * @return string
     */
    private function renderChangeCurrencyComment(array $data)
    {
        return __(
            '%1 changed the credit currency from %2 to %3 at the conversion rate of %2/%3 %4.',
            $data['user_name'],
            $data['currency_from'],
            $data['currency_to'],
            $data['currency_rate']
        );
    }
}
