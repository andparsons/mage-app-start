<?php
namespace Magento\Company\Ui\Component\Listing\Role\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!$this->authorization->isAllowed('Magento_Company::roles_edit') || !isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $count = count($dataSource['data']['items']);
        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['duplicate'] = [
                'href' => $this->urlBuilder->getUrl(
                    'company/role/edit',
                    ['duplicate_id' => $item['role_id']]
                ),
                'label' => __('Duplicate'),
                'hidden' => false,
            ];
            $item[$this->getData('name')]['edit'] = [
                'href' => $this->urlBuilder->getUrl(
                    'company/role/edit',
                    ['id' => $item['role_id']]
                ),
                'label' => __('Edit'),
                'hidden' => false,
            ];
            if ($count > 1) {
                $item[$this->getData('name')]['delete'] = [
                    'href' => '#',
                    'label' => __('Delete'),
                    'hidden' => false,
                    'type' => 'delete-role',
                    'options' => [
                        'deleteUrl' => $this->urlBuilder->getUrl(
                            'company/role/delete',
                            ['id' => $item['role_id']]
                        ),
                        'deletable' => !(int)$item['users_count'],
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
