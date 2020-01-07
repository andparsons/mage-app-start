<?php
namespace Magento\Framework\Data\Test\Unit\Stub;

use Magento\Framework\Data\AbstractSearchCriteriaBuilder;

class SearchCriteriaBuilder extends AbstractSearchCriteriaBuilder
{
    /**
     * @return string|void
     */
    public function init()
    {
        $this->resultObjectInterface = \Magento\Framework\Api\CriteriaInterface::class;
    }
}
