<?php
namespace Magento\Payment\Test\Unit\Model\Method\Specification;

/**
 * Composite Test
 */
class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Payment\Model\Method\Specification\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    protected function setUp()
    {
        $this->factoryMock = $this->createMock(\Magento\Payment\Model\Method\Specification\Factory::class);
    }

    /**
     * @param array $specifications
     * @return \Magento\Payment\Model\Method\Specification\Composite
     */
    protected function createComposite($specifications = [])
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        return $objectManager->getObject(
            \Magento\Payment\Model\Method\Specification\Composite::class,
            ['factory' => $this->factoryMock, 'specifications' => $specifications]
        );
    }

    /**
     * @param bool $firstSpecificationResult
     * @param bool $secondSpecificationResult
     * @param bool $compositeResult
     * @dataProvider compositeDataProvider
     */
    public function testComposite($firstSpecificationResult, $secondSpecificationResult, $compositeResult)
    {
        $method = 'method-name';

        $specificationFirst = $this->createMock(\Magento\Payment\Model\Method\SpecificationInterface::class);
        $specificationFirst->expects(
            $this->once()
        )->method(
            'isSatisfiedBy'
        )->with(
            $method
        )->will(
            $this->returnValue($firstSpecificationResult)
        );

        $specificationSecond = $this->createMock(\Magento\Payment\Model\Method\SpecificationInterface::class);
        $specificationSecond->expects(
            $this->any()
        )->method(
            'isSatisfiedBy'
        )->with(
            $method
        )->will(
            $this->returnValue($secondSpecificationResult)
        );

        $this->factoryMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            'SpecificationFirst'
        )->will(
            $this->returnValue($specificationFirst)
        );
        $this->factoryMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            'SpecificationSecond'
        )->will(
            $this->returnValue($specificationSecond)
        );

        $composite = $this->createComposite(['SpecificationFirst', 'SpecificationSecond']);

        $this->assertEquals(
            $compositeResult,
            $composite->isSatisfiedBy($method),
            'Composite specification is not satisfied by payment method'
        );
    }

    /**
     * @return array
     */
    public function compositeDataProvider()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false]
        ];
    }
}
