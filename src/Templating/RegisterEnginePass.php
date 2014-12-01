<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking\Templating;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This compiler pass adds additional templating engines to the delegate engine
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Templating
 */
class RegisterEnginePass implements CompilerPassInterface
{
    /**
     * @var string
     */
    protected $delegateEngineService;

    /**
     * @var string
     */
    protected $engineTag;

    public function __construct($delegateEngineService = 'templating.engine.delegate', $engineTag = 'templating_engine')
    {
        $this->delegateEngineService = $delegateEngineService;
        $this->engineTag = $engineTag;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->delegateEngineService)) {
            return;
        }

        $delegateEngine = $container->getDefinition($this->delegateEngineService);

        foreach ($container->findTaggedServiceIds($this->engineTag) as $id => $attributes) {
            $delegateEngine->addMethodCall('addEngine', array(new Reference($id)));
        }
    }
} 