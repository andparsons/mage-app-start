<?php
namespace Magento\Solr\Model;

class SolariumFactory implements \Magento\Solr\Model\AdapterFactoryInterface
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createAdapter()
    {
        return $this->_objectManager->create(\Magento\Solr\Model\Adapter\Solarium::class);
    }
}
