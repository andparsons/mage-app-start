<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Convert old BlueFoot format to PageBuilder format
 */
class TreeConverter
{
    /**
     * @var RendererPool
     */
    private $rendererPool;

    /**
     * @var ChildrenExtractorPool
     */
    private $childrenExtractorPool;

    /**
     * @var ChildrenRendererPool
     */
    private $childrenRendererPool;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $unseparatableContentTypes;

    /**
     * @param RendererPool $rendererPool
     * @param ChildrenExtractorPool $childrenExtractorPool
     * @param ChildrenRendererPool $childrenRendererPool
     * @param Json $serializer
     * @param LoggerInterface $logger
     * @param array $unseparatableContentTypes
     */
    public function __construct(
        RendererPool $rendererPool,
        ChildrenExtractorPool $childrenExtractorPool,
        ChildrenRendererPool $childrenRendererPool,
        Json $serializer,
        LoggerInterface $logger,
        array $unseparatableContentTypes = []
    ) {
        $this->rendererPool = $rendererPool;
        $this->childrenExtractorPool = $childrenExtractorPool;
        $this->childrenRendererPool = $childrenRendererPool;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->unseparatableContentTypes = $unseparatableContentTypes;
    }

    /**
     * Render JSON format to new master format
     *
     * @param string $string
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws UnableMigrateWithOutParentException
     */
    public function convert(string $string) : string
    {
        $jsonTree = $this->serializer->unserialize($string);
        $html = '';
        if (isset($jsonTree['type']) || isset($jsonTree['contentType'])) {
            $jsonTree = [$jsonTree];
        }
        foreach ($jsonTree as $childIndex => $treeItem) {
            $html .= $this->convertTreeItem($treeItem, ['childIndex' => $childIndex]);
        }
        return $html;
    }

    /**
     * Convert content type item
     *
     * @param array $itemData
     * @param array $additionalData
     * @param array $children
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws UnableMigrateWithOutParentException
     */
    private function convertTreeItem(array $itemData, array $additionalData = [], array $children = []) : string
    {
        $contentType = isset($itemData['type']) ? $itemData['type'] : $itemData['contentType'];
        $renderer = $this->rendererPool->getRenderer($contentType);
        if (empty($children)) {
            $childrenExtractor = $this->childrenExtractorPool->getExtractor($contentType);
            $itemChildren = $childrenExtractor->extract($itemData);
        } else {
            $itemChildren = $children;
        }
        if (!empty($itemChildren)) {
            try {
                $childRenderer = $this->childrenRendererPool->getChildrenRenderer($contentType);
                $childrenHtml = $childRenderer->render(
                    $itemChildren,
                    function ($childItem, $childIndex, $children = []) use ($additionalData) {
                        return $this->convertTreeItem(
                            $childItem,
                            [
                                'childIndex' => $childIndex,
                                'parentChildIndex' => $additionalData['childIndex'],
                            ],
                            $children
                        );
                    }
                );
                return $this->processItemRendering(
                    $renderer,
                    $itemData,
                    [
                        'children' => $childrenHtml,
                        'childIndex' => $additionalData['childIndex'],
                    ]
                );
            } catch (UnableMigrateWithOutParentException $exception) {
                $defaultRenderer = $this->rendererPool->getRenderer('default');
                // If the children have been explicitly provided to the function we need to set them into the item
                if (!empty($children)) {
                    $itemData['children'] = $children;
                }
                return $this->processItemRendering(
                    $defaultRenderer,
                    $itemData
                );
            }
        }
        return $this->processItemRendering(
            $renderer,
            $itemData,
            $additionalData
        );
    }

    /**
     * Process item rendering
     *
     * @param RendererInterface $renderer
     * @param array $itemData
     * @param array $itemAdditionalData
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws UnableMigrateWithOutParentException
     */
    private function processItemRendering(
        RendererInterface $renderer,
        array $itemData,
        array $itemAdditionalData = []
    ) : string {
        $defaultRenderer = $this->rendererPool->getRenderer('default');

        try {
            // Do not migrate content type if entity is missing required attributes
            set_error_handler(
                function ($errorNumber, $errorString, $errorFile, $errorLine) use ($itemData) {
                    restore_error_handler();
                    $errorMessage = '#' . $errorNumber. ' '
                        . $errorString . ' in ' . $errorFile . ' on line ' . $errorLine . '.';
                    $this->logger->error($errorMessage);
                    throw new \UnexpectedValueException(
                        'Entity data is invalid: "'
                        . $this->serializer->serialize($itemData)
                        . "\". \n" . $errorMessage
                    );
                },
                E_NOTICE
            );
            $html = $renderer->render($itemData, $itemAdditionalData);
            restore_error_handler();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $html = $this->handleRenderException(
                $exception,
                $defaultRenderer,
                $itemData,
                $itemAdditionalData
            );
        }

        return $html;
    }

    /**
     * Handle a render exception
     *
     * Migrate as html if content type can be migrated without parent, otherwise throw
     * UnableMigrateWithOutParentException.
     *
     * @param \Exception $exception
     * @param RendererInterface $defaultRenderer
     * @param array $itemData
     * @param array $itemAdditionalData
     * @return string
     * @throws UnableMigrateWithOutParentException
     * @throws NoSuchEntityException
     */
    private function handleRenderException(
        \Exception $exception,
        RendererInterface $defaultRenderer,
        array $itemData,
        array $itemAdditionalData
    ): string {
        if ($this->isUnseparatableContentType($itemData)) {
            throw new UnableMigrateWithOutParentException(
                __('Content type can not be migrated with out parent.'),
                $exception
            );
        }
        return $defaultRenderer->render($itemData, $itemAdditionalData);
    }

    /**
     * Check whether content type is un-separatable
     *
     * It can not be part of another content type and parent content type can not contain content types of different
     * type
     *
     * @param array $itemData
     * @return bool
     */
    private function isUnseparatableContentType(array $itemData)
    {
        return in_array(
            isset($itemData['type']) ? $itemData['type'] : $itemData['contentType'],
            $this->unseparatableContentTypes
        );
    }
}
