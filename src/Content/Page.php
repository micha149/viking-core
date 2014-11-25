<?php
/**
 * Created by PhpStorm.
 * User: micha149
 * Date: 23.11.14
 * Time: 11:58
 */

namespace Viking\Content;


class Page {

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $path;

    public function __construct($uri, $path) {
        $this->uri = $uri;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
} 