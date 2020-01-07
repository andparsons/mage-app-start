<?php
namespace Magento\Test\Di\Aggregate;

class WithOptional
{
    public $parent;

    public $child;

    /**
     * WithOptional constructor.
     * @param \Magento\Test\Di\DiParent|null $parent
     * @param \Magento\Test\Di\Child|null $child
     */
    public function __construct(\Magento\Test\Di\DiParent $parent = null, \Magento\Test\Di\Child $child = null)
    {
        $this->parent = $parent;
        $this->child = $child;
    }
}
