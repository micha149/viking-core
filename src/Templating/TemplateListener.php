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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class TemplateListener
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Templating
 */
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
        $pageType = $request->attributes->get('_content')->getType();

        if ($request->attributes->get('_template')) {
            return;
        }

        $request->attributes->set('_template', $this->guesser->guessTemplateName($pageType));
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) {
        $request = $event->getRequest();
        $template = $request->attributes->get('_template');
        $parameters = $event->getControllerResult();
        $event->setResponse(new Response($this->engine->render($template, $parameters)));
    }
}