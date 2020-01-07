<?php

namespace Magento\NegotiableQuote\Block\Quote\Item\Actions;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;

/**
 * Remove link action block.
 *
 * @api
 * @since 100.0.0
 */
class Remove extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postDataHelper;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param RestrictionInterface $restriction
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        RestrictionInterface $restriction,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->postDataHelper = $postDataHelper;
        $this->restriction = $restriction;
        $this->authorization = $authorization;
    }

    /**
     * Returns post data for removing item from quote.
     *
     * @return string
     */
    public function getRemoveParams()
    {
        $url = $this->getUrl(
            '*/*/itemDelete',
            [
                'quote_id' => $this->getRequest()->getParam('quote_id'),
                'quote_item_id' => $this->getItem()->getId()
            ]
        );

        return $this->postDataHelper->getPostData($url);
    }

    /**
     * Perform permissions check.
     *
     * @return bool
     */
    public function isAllowedManage()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }

    /**
     * Is submit available.
     *
     * @return bool
     */
    public function isSubmitAvailable()
    {
        return $this->restriction->canSubmit();
    }
}
