<?php

namespace Viking\Templating;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Templating\EngineInterface;

class TemplateListener implements EventSubscriberInterface {

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var TemplateGuesserInterface
     */
    private $guesser;

    /**
     * Constructor.
     *
     * @param EngineInterface $engine
     * @param TemplateGuesserInterface $guesser
     */
    public function __construct(EngineInterface $engine, TemplateGuesserInterface $guesser)
    {
        $this->engine = $engine;
        $this->guesser = $guesser;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -128),
            KernelEvents::VIEW => 'onKernelView',
        );
    }

    public function onKernelController(FilterControllerEvent $event) {
        $request = $event->getRequest();

        if ($request->attributes->get('_template')) {
            return;
        }

        $request->attributes->set('_template', $this->guesser->guessTemplateName());
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) {
        $request = $event->getRequest();
        $template = $request->attributes->get('_template');
        $parameters = $event->getControllerResult();
        $event->setResponse(new Response($this->engine->render($template, $parameters)));
    }
}