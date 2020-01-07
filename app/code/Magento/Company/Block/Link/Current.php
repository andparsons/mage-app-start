<?php

namespace Magento\Company\Block\Link;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class Current.
 *
 * @api
 * @since 100.0.0
 */
class Current extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{
    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var string
     */
    private $resource;

    /**
     * Current constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Company\Model\CompanyContext $companyContext,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->companyContext = $companyContext;
        if (isset($data['resource'])) {
            $this->resource = $data['resource'];
        }
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isVisible()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        return $this->companyContext->isResourceAllowed($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
