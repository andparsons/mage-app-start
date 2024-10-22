<?php
namespace Magento\Framework\View\Element\Template\File;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Resolver
 * @package Magento\Framework\View\Element\Template\File
 */
class Resolver
{
    /**
     * Template files map
     *
     * @var []
     */
    protected $_templateFilesMap = [];

    /**
     * View filesystem
     *
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Framework\View\FileSystem $viewFileSystem,
        Json $serializer = null
    ) {
        $this->_viewFileSystem = $viewFileSystem;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Get template filename
     *
     * @param string $template
     * @param [] $params
     * @return string|bool
     */
    public function getTemplateFileName($template, $params = [])
    {
        $key = $template . '_' . $this->serializer->serialize($params);
        if (!isset($this->_templateFilesMap[$key])) {
            $this->_templateFilesMap[$key] = $this->_viewFileSystem->getTemplateFileName($template, $params);
        }
        return $this->_templateFilesMap[$key];
    }
}
