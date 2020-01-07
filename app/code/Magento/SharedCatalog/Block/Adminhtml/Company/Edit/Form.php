<?php
namespace Magento\SharedCatalog\Block\Adminhtml\Company\Edit;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Catalog company form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder
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
     * @param \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder,
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', []);

        $fieldset->addField(
            'shared_catalog_id',
            'hidden',
            [
                'name' => 'shared_catalog_id',
                'required' => true
            ]
        );

        $form->setValues($this->getValues());

        $form->setUseContainer(true)
            ->setId('edit_form')
            ->setAction($this->urlBuilder->getUrl('shared_catalog/sharedCatalog_company/save'))
            ->setMethod('post');
        $this->setForm($form);
    }

    /**
     * Get form values.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getValues()
    {
        $sharedCatalogId = $this->getCurrentSharedCatalog()->getId();
        return [
            SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId
        ];
    }

    /**
     * Get current shared catalog.
     *
     * @return \Magento\SharedCatalog\Model\SharedCatalog
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentSharedCatalog()
    {
        $sharedCatalogId = $this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        return $this->sharedCatalogRepository->get($sharedCatalogId);
    }
}
