<?php

namespace Magento\Framework\ObjectManager\Helper;

/**
 * Helper for classes which implement Composite pattern.
 */
class Composite
{
    /**
     * @param array $declaredComponents Array of the components which should be registered in the following format:
     * <pre>
     * [
     *      ['type' => $firstComponentObject, 'sortOrder' => 15],
     *      ['type' => $secondComponentObject, 'sortOrder' => 10],
     *      ...
     * ]
     * </pre>
     * @return array Array of components declarations. Items are sorted and misconfigured ones are removed.
     */
    public function filterAndSortDeclaredComponents($declaredComponents)
    {
        /** Eliminate misconfigured components */
        $declaredComponents = array_filter(
            $declaredComponents,
            function ($component) {
                return (isset($component['type']) && isset($component['sortOrder']));
            }
        );
        /** Sort all components according to the provided sort order */
        uasort(
            $declaredComponents,
            function ($firstComponent, $secondComponent) {
                return (int)$firstComponent['sortOrder'] <=> (int)$secondComponent['sortOrder'];
            }
        );
        $declaredComponents = array_values($declaredComponents);
        return $declaredComponents;
    }
}
