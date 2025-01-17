<?php

/**
 * Automatic cleanup of test case's properties, it isn't needed to unset properties manually in tearDown() anymore
 */
namespace Magento\TestFramework\Workaround\Cleanup;

class TestCaseProperties
{
    /**
     * Clear test method properties after each test suite
     *
     * @param  \PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite)
    {
        $tests = $suite->tests();

        foreach ($tests as $test) {
            $reflectionClass = new \ReflectionClass($test);
            $properties = $reflectionClass->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($test);
                if (is_object($value) && method_exists($value, '__destruct') && is_callable([$value, '__destruct'])) {
                    $value->__destruct();
                }
                $property->setValue($test, null);
            }
        }
    }
}
