<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product\Formatter;

use Magento\Catalog\Model\View\Asset\ImageFactory;

/**
 * Class ImageFormatter
 */
class ImageFormatter
{
    /**
     * @var ImageFactory
     */
    private $imageFactory;
    /**
     * @var array
     */
    private $images;

    /**
     * ImageFieldFormatter constructor.
     *
     * @param ImageFactory $imageFactory
     * @param array $images
     */
    public function __construct(
        ImageFactory $imageFactory,
        array $images = ['image', 'smallImage', 'thumbnail', 'swatchImage']
    ) {
        $this->imageFactory = $imageFactory;
        $this->images = $images;
    }

    /**
     * Format provider data
     *
     * @param array $row
     * @return array
     */
    public function format(array $row) : array
    {
        foreach ($this->images as $image) {
            if (isset($row[$image])) {
                $asset = $this->imageFactory->create(
                    [
                        'miscParams' => [],
                        'filePath' => $row[$image]
                    ]
                );
                $imageUrl = $asset->getUrl();
                $row[$image] = [
                    'url' => $imageUrl,
                    'label' => isset($row[$image . '_label']) ? $row[$image . '_label'] : null
                ];
            }
        }
        return $row;
    }
}
