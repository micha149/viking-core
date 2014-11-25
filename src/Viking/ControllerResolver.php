<?php
/**
 * Created by PhpStorm.
 * User: micha149
 * Date: 18.11.14
 * Time: 19:47
 */

namespace Viking;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseResolver;

class ControllerResolver extends BaseResolver {

    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        $attributes = $request->attributes->all();
        $arguments = array();

        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($attributes['_content'] && $param->getClass() && $param->getClass()->isInstance($attributes['_content'])) {
                $arguments[] = $attributes['_content'];
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }

        return $arguments;
    }
}