<?php

declare(strict_types=1);

namespace Magento\PageBuilder\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class WidgetInitializer provides configuration for content types widgets need to be loaded on frontend
 *
 * @api
 */
class WidgetInitializer extends Template
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var \Magento\PageBuilder\Model\WidgetInitializerConfig
     */
    private $config;

    /**
     * WidgetInitializer constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
     * @param \Magento\PageBuilder\Model\WidgetInitializerConfig $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        \Magento\PageBuilder\Model\WidgetInitializerConfig $config,
        array $data = []
    ) {
        $this->jsonSerializer = $jsonEncoder;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Returns config for widgets initializer component.
     * @return string
     * @api
     */
    public function getConfig() : string
    {
        return $this->jsonSerializer->serialize($this->config->getConfig());
    }
}
