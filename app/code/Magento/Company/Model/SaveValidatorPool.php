<?php

namespace Magento\Company\Model;

use Magento\Company\Api\Data\CompanyInterface;

/**
 * Company save validator pool.
 */
class SaveValidatorPool
{
    /**
     * @var SaveValidatorInterface[]
     */
    private $validators;

    /**
     * @var SaveValidatorInterfaceFactory
     */
    private $saveValidatorFactory;

    /**
     * @param SaveValidatorInterfaceFactory $saveValidatorFactory
     * @param SaveValidatorInterface[] $validators [optional]
     */
    public function __construct(
        SaveValidatorInterfaceFactory $saveValidatorFactory,
        $validators = []
    ) {
        $this->saveValidatorFactory = $saveValidatorFactory;
        $this->validators = $validators;
    }

    /**
     * Checks if provided data for a company is correct by executing save validators.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\Company\Api\Data\CompanyInterface $initialCompany
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(
        CompanyInterface $company,
        CompanyInterface $initialCompany
    ) {
        $exception = new \Magento\Framework\Exception\InputException();
        foreach ($this->validators as $validatorClass) {
            $validator = $this->saveValidatorFactory->create(
                $validatorClass,
                [
                    'company' => $company,
                    'initialCompany' => $initialCompany,
                    'exception' => $exception
                ]
            );
            if (!$validator instanceof \Magento\Company\Model\SaveValidatorInterface) {
                throw new \InvalidArgumentException(__(
                    'Type %1 is not an instance of %2',
                    get_class($validator),
                    \Magento\Company\Model\SaveValidatorInterface::class
                ));
            }
            $validator->execute();
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
