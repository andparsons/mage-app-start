<?php

namespace Magento\NegotiableQuote\Model\Restriction;

/**
 * Creates RestrictionInterface objects filled with Quote objects.
 */
class RestrictionInterfaceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Cache of already created RestrictionInterface objects.
     *
     * @var array
     */
    private $cache = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance filled with Quote instance.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    public function create(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        if (!isset($this->cache[$quote->getId()])) {
            $restriction = $this->objectManager->create(
                \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class,
                ['quote' => $quote]
            );
            $this->cache[$quote->getId()] = $restriction;
        }

        return $this->cache[$quote->getId()];
    }
}
