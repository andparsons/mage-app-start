<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter\Renderer;

use Magento\PageBuilderDataMigration\Setup\DataConverter\RendererInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageBuilderDataMigration\Setup\DataConverter\Format;

/**
 * Render non PageBuilder content type to html content type with embedded data
 */
class Unmigrated implements RendererInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function render(array $itemData, array $additionalData = []) : string
    {
        $comment = '&lt;!--' . Format::UNMIGRATED_KEY . '="' . $this->serializer->serialize($itemData) . '"--&gt;';
        return '<div data-content-type="html" data-appearance="default" data-element="main">'
            . $comment
            . '</div>';
    }
}
