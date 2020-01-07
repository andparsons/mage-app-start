<?php
namespace Magento\Company\Ui\Component;

/**
 * Class MassAction
 */
class MassAction extends \Magento\Ui\Component\MassAction
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        if (isset($config['actions'])) {
            $this->sort($config['actions']);
        }
        $this->setData('config', $config);
    }

    /**
     * Sort actions
     *
     * @param array $actions
     * @return array
     */
    protected function sort(array &$actions)
    {
        usort($actions, function (array $a, array $b) {
            $a['sortOrder'] = isset($a['sortOrder']) ? $a['sortOrder'] : 0;
            $b['sortOrder'] = isset($b['sortOrder']) ? $b['sortOrder'] : 0;

            return $a['sortOrder'] - $b['sortOrder'];
        });

        return $actions;
    }
}
