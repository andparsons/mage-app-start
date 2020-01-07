<?php

namespace Magento\Company\Model;

use Magento\Company\Api\Data\CompanyInterface;

/**
 * Company save handler pool.
 */
class SaveHandlerPool
{
    /**
     * @var SaveHandlerInterface[]
     */
    private $handlers;

    /**
     * @param SaveHandlerInterface[] $handlers [optional]
     */
    public function __construct(
        $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * Execute save handlers.
     *
     * @param CompanyInterface $company
     * @param CompanyInterface $initialCompany
     * @return array CompanyInterface errors
     */
    public function execute(CompanyInterface $company, CompanyInterface $initialCompany)
    {
        foreach ($this->handlers as $saveHandler) {
            if (!$saveHandler instanceof \Magento\Company\Model\SaveHandlerInterface) {
                throw new \InvalidArgumentException(__(
                    'Type %1 is not an instance of %2',
                    get_class($saveHandler),
                    \Magento\Company\Model\SaveHandlerInterface::class
                ));
            }
            $saveHandler->execute($company, $initialCompany);
        }
    }
}
