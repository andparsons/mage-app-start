<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Modifier for duplicating. Change data and save url of DataProvider.
 */
class Duplicate implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if ($this->request->getActionName() == 'duplicate') {
            foreach ($data['items'] as &$item) {
                $item['duplicate_id'] = $item[SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM];
                unset($item[SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM]);
                $item['catalog_details']['type'] = SharedCatalogInterface::TYPE_CUSTOM;
                $item['catalog_details']['created_at'] = null;
                $item['catalog_details']['customer_group_id'] = null;
                $item['catalog_details']['name'] = __('Duplicate of %1', $item['catalog_details']['name']);
            }
            $data['config']['submit_url'] = $this->urlBuilder->getUrl('shared_catalog/sharedCatalog/duplicatePost');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
