<?php
namespace Bolt\Storage\Entity;

/**
 * Trait class for ContentType routing.
 *
 * This is a breakout of the old Bolt\Content class and serves two main purposes:
 *   * Maintain backward compatibility for Bolt\Content through the remainder of
 *     the 2.x development/release life-cycle
 *   * Attempt to break up former functionality into sections of code that more
 *     resembles Single Responsibility Principles
 *
 * These traits should be considered transitional, the functionality in the
 * process of refactor, and not representative of a valid approach.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
trait ContentRouteTrait
{
    /**
     * Retrieves the first route applicable to the content as a two-element array consisting of the binding and the
     * route array. Returns `null` if there is no applicable route.
     *
     * @return array|null
     */
    protected function getRoute()
    {
        $allroutes = $this->app['config']->get('routing');

        // First, try to find a custom route that's applicable
        foreach ($allroutes as $binding => $route) {
            if ($this->isApplicableRoute($route)) {
                return [$binding, $route];
            }
        }

        // Just return the 'generic' contentlink route.
        if (!empty($allroutes['contentlink'])) {
            return ['contentlink', $allroutes['contentlink']];
        }

        return null;
    }

    /**
     * Build a Contenttype's route parameters
     *
     * @param array $route
     *
     * @return array
     */
    protected function getRouteRequirementParams(array $route)
    {
        $params = [];
        if (isset($route['requirements'])) {
            foreach ($route['requirements'] as $fieldName => $requirement) {
                if ('\d{4}-\d{2}-\d{2}' === $requirement) {
                    // Special case, if we need to have a date
                    $params[$fieldName] = substr($this->values[$fieldName], 0, 10);
                } elseif (isset($this->taxonomy[$fieldName])) {
                    // Turn something like '/chapters/meta' to 'meta'. Note: we use
                    // two temp vars here, to prevent "Only variables should be passed
                    // by reference"-notices.
                    $tempKeys = array_keys($this->taxonomy[$fieldName]);
                    $tempValues = explode('/', array_shift($tempKeys));
                    $params[$fieldName] = array_pop($tempValues);
                } elseif (isset($this->values[$fieldName])) {
                    $params[$fieldName] = $this->values[$fieldName];
                } else {
                    // unkown
                    $params[$fieldName] = null;
                }
            }
        }

        return $params;
    }
}
