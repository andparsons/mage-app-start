<?php
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Signifyd\Api\CaseRepositoryInterface;

/**
 * Generates message based on previous and current Case scores.
 */
class CaseRescore implements GeneratorInterface
{
    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * CaseRescore constructor.
     *
     * @param CaseRepositoryInterface $caseRepository
     */
    public function __construct(CaseRepositoryInterface $caseRepository)
    {
        $this->caseRepository = $caseRepository;
    }

    /**
     * @inheritdoc
     */
    public function generate(array $data)
    {
        if (empty($data['caseId'])) {
            throw new GeneratorException(__('The "%1" should not be empty.', 'caseId'));
        }

        $caseEntity = $this->caseRepository->getByCaseId($data['caseId']);

        if ($caseEntity === null) {
            throw new GeneratorException(__('Case entity not found.'));
        }

        return __(
            'Case Update: New score for the order is %1. Previous score was %2.',
            !empty($data['score']) ? $data['score'] : 0,
            $caseEntity->getScore()
        );
    }
}
