<?php

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element\DataType\Media;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Framework\File\Size;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Image Form UI Component
 */
class Image extends Media
{
    const NAME = 'image';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Size
     */
    private $fileSize;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param Size $fileSize
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        Size $fileSize,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->fileSize = $fileSize;
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        // dynamically set max file size based on php ini config if not present in XML
        $maxFileSize = min(array_filter([
            $this->getConfiguration()['maxFileSize'] ?? 0,
            $this->fileSize->getMaxFileSize()
        ]));

        $data = array_replace_recursive(
            $this->getData(),
            [
                'config' => [
                    'maxFileSize' => $maxFileSize,
                    'mediaGallery' => [
                        'openDialogUrl' => $this->getContext()->getUrl('cms/wysiwyg_images/index', ['_secure' => true]),
                        'openDialogTitle' => $this->getConfiguration()['openDialogTitle'] ?? __('Insert Images...'),
                        'initialOpenSubpath' => $this->getConfiguration()['initialMediaGalleryOpenSubpath'],
                        'storeId' => $this->storeManager->getStore()->getId(),
                    ],
                ],
            ]
        );

        $this->setData($data);
        parent::prepare();
    }
}
