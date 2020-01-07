<?php
namespace Magento\SharedCatalog\Ui\Component\MassAction;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Companies mass action
 */
class Companies extends \Magento\Ui\Component\MassAction
{
    /**#@+
     * Company mass assign route path
     */
    const ROUTE_PATH_COMPANY_MASSASSIGN = 'shared_catalog/sharedCatalog/company_massAssign';
    const ROUTE_PATH_COMPANY_MASSASSIGN_VALIDATE = 'shared_catalog/sharedCatalog/company_massAssign_validate';
    /**#@-*/

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->prepareClientUrl(
            'assignActionConfig',
            'saveUrl',
            self::ROUTE_PATH_COMPANY_MASSASSIGN
        );
        $this->prepareClientUrl(
            'assignActionConfig',
            'validateUrl',
            self::ROUTE_PATH_COMPANY_MASSASSIGN_VALIDATE
        );

        $this->prepareClientUrl(
            'unassignActionConfig',
            'saveUrl',
            self::ROUTE_PATH_COMPANY_MASSASSIGN
        );
        $this->prepareClientUrl(
            'unassignActionConfig',
            'validateUrl',
            self::ROUTE_PATH_COMPANY_MASSASSIGN
        );

        parent::prepare();
    }

    /**
     * Prepares client save url
     *
     * @param string $configKey
     * @param string $configUrlKey
     * @param string $routePath
     * @return $this
     */
    protected function prepareClientUrl($configKey, $configUrlKey, $routePath)
    {
        if (!isset($this->_data['config'][$configKey]['clientConfig'])) {
            return $this;
        }
        $this->_data['config'][$configKey]['clientConfig'][$configUrlKey] =
            $this->urlBuilder->getUrl($routePath);

        return $this;
    }
}
