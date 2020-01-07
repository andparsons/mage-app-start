<?php

namespace Magento\Mtf\App\State;

/**
 * Interface StateHandlerInterface
 */
interface StateHandlerInterface
{
    /**
     * Perform app state change before run
     *
     * @param AbstractState $state
     * @return mixed
     */
    public function execute(AbstractState $state);
}
