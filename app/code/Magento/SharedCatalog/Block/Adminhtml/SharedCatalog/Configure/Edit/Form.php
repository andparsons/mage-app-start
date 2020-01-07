<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Configure\Edit;

use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Catalog configure form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param UrlBuilder $urlBuilder
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        UrlBuilder $urlBuilder,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->urlBuilder = $urlBuilder;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Prepare form for render.
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Catalog Structure')]);

        $fieldset->addField(
            'catalog_id',
            'hidden',
            [
                'name' => 'catalog_id',
                'label' => __('Catalog Id'),
                'title' => __('Catalog Id'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'store_id',
            'hidden',
            [
                'name' => 'store_id',
                'label' => __('Store Id'),
                'title' => __('Store Id'),
                'required' => false
            ]
        );

        $form->setValues($this->getValues());

        $form->setUseContainer(true)
            ->setId('edit_form')
            ->setAction($this->urlBuilder->getUrl('shared_catalog/sharedCatalog_configure/save'))
            ->setMethod('post');
        $this->setForm($form);
    }

    /**
     * Get form values.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getValues()
    {
        $sharedCatalogId = $this->_request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);

        return [
            'catalog_id' => $sharedCatalog->getId(),
            'store_id' => $sharedCatalog->getStoreId()
        ];
    }
}
