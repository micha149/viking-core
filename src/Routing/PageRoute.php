<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking\Routing;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;
use Viking\Content\Page;

/**
 * Class PageRoute
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Routing
 */
class PageRoute extends Route implements RouteObjectInterface {

    /**
     * @var Page
     */
    private $page;

    /**
     * @param Page $page
     * @param array $defaults
     * @param array $requirements
     * @param array $options
     * @param string $host
     * @param array $schemes
     * @param array $methods
     * @param string $condition
     */
    public function __construct (Page $page, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array(), $condition = '') {
        $this->page = $page;
        parent::__construct($page->getUri(), $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    /**
     * Get the content document this route entry stands for. If non-null,
     * the ControllerClassMapper uses it to identify a controller and
     * the content is passed to the controller.
     *
     * If there is no specific content for this url (i.e. its an "application"
     * page), may return null.
     *
     * @return object the document or entity this route entry points to
     */
    public function getContent()
    {
        return $this->page;
    }

    /**
     * Get the route key.
     *
     * This key will be used as route name instead of the symfony core compatible
     * route name and can contain any characters.
     *
     * Return null if you want to use the default key.
     *
     * @return string the route name
     */
    public function getRouteKey()
    {
        return $this->page->getPath();
    }
}