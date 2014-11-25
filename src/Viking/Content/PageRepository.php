<?php

namespace Viking\Content;

use Symfony\Component\Finder\Finder;
use Viking\Content\Exception\ContentNotFoundException;

class PageRepository {

    /**
     * @var string
     */
    private $path;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @param string $path
     */
    public function __construct($path) {
        $this->path = $path;
    }

    protected function createFinder() {
        $finder = new Finder();
        $finder->in($this->path);
        return $finder;
    }

    /**
     * @param string $uri
     * @return Page
     * @throws ContentNotFoundException
     */
    public function findByUri($uri) {
        $finder = $this->createFinder();
        $directories = iterator_to_array($finder->path($this->createUriRegex($uri))->directories());

        if (count($directories) == 0) {
            throw new ContentNotFoundException('No Page found for uri ' . $uri);
        }

        return new Page($uri, $directories[0]);
    }

    protected function createUriRegex($uri) {
        $parts = explode('/', trim($uri, '/'));

        for ($i = 0; $i < count($parts); $i++) {
            $parts[$i] = '([0-9]+-)?' . $parts[$i];
        }

        return '/' . implode('\/', $parts) . '$/';
    }
} 