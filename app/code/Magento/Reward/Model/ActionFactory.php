<?php
namespace Magento\Reward\Model;

use Magento\Reward\Model\Action\AbstractAction;

class ActionFactory
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
     * Create new action object
     *
     * @param string $type
     * @param array $data
     * @return AbstractAction
     */
    public function create($type, array $data = [])
    {
        return $this->_objectManager->create($type, $data);
    }
}
