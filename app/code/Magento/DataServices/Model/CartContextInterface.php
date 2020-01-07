<?php
declare(strict_types=1);

namespace Magento\DataServices\Model;

/**
 * Interface for Cart Context model
 *
 * @api
 */
interface CartContextInterface
{
    /**
     * Return cart context for data services events
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getContextData(): array;
}
