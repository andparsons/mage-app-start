<?php
namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

class IncompatibleInterface
{
    /**
     * @param $subject
     * @param $invocationResult
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetName($subject, $invocationResult)
    {
        return $invocationResult . '!';
    }
}
