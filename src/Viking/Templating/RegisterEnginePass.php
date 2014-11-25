<?php

namespace Viking\Templating;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This compiler pass adds additional route enhancers
 * to the dynamic router.
 *
 * @author Daniel Leech <dan.t.leech@gmail.com>
 * @author Nathaniel Catchpole (catch)
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