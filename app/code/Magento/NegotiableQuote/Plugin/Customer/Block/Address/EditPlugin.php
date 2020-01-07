<?php

namespace Magento\NegotiableQuote\Plugin\Customer\Block\Address;

use Magento\Framework\UrlInterface;

/**
 * Class EditPlugin
 */
class EditPlugin
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * EditPlugin constructor.
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Customer\Block\Address\Edit $subject
     * @param string $url
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSaveUrl(
        \Magento\Customer\Block\Address\Edit $subject,
        $url
    ) {
        return $this->urlBuilder->getUrl(
            'customer/address/formPost',
            ['_secure' => true, 'id' => $subject->getAddress()->getId(), '_current' => ['quoteId']]
        );
    }
}
