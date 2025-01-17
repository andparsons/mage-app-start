<?php

declare(strict_types=1);

namespace Magento\PageBuilder\Block\Adminhtml\Stage;

use Magento\Framework\View\Element\Template;
use Magento\RequireJs\Model\FileManager;
use Magento\PageBuilder\Model\Stage\Config;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Render
 */
class Render extends Template
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var Config
     */
    private $pageBuilderConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Template\Context $context
     * @param FileManager $fileManager
     * @param Config $config
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FileManager $fileManager,
        Config $config,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fileManager = $fileManager;
        $this->pageBuilderConfig = $config;
        $this->json = $json;
    }

    /**
     * Generate the URL to RequireJS
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequireJsUrl() : string
    {
        $asset = $this->_assetRepo->createAsset('requirejs/require.js');
        return $asset->getUrl();
    }

    /**
     * Retrieve the URL to the RequireJS Config file
     *
     * @return string
     */
    public function getRequireJsConfigUrl() : string
    {
        $requireJsConfig = $this->fileManager->createRequireJsConfigAsset();
        return $requireJsConfig->getUrl();
    }

    /**
     * Retrieve the Page Builder's config
     *
     * @return array
     */
    public function getPageBuilderConfig() : string
    {
        return $this->json->serialize($this->pageBuilderConfig->getConfig());
    }
}
