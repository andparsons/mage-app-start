<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit;

/**
 * Class GenericButton
 * @package Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit
 */
class GenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $context->getRequest();
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', array $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
