<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking\Content;

use KzykHys\FrontMatter\Document;
use KzykHys\FrontMatter\FrontMatter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Viking\Content\Exception\ContentNotFoundException;
use Webmozart\PathUtil\Path;

/**
 * Class PageRepository
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Content
 */
class PageRepository {

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * Returns the page for the given uri
     *
     * @param string $uri
     * @return Page
     * @throws ContentNotFoundException
     */
    public function findOneByUri($uri) {
        $finder = new Finder();

        $finder->in($this->path)
            ->path($this->createUriRegex($uri))
            ->directories();

        $directories = iterator_to_array($finder);

        if (count($directories) == 0) {
            throw new ContentNotFoundException('No Page found for uri ' . $uri);
        }

        return new Page($uri, $this->getPageTypeForPath(reset($directories)), null, $this);
    }

    /**
     * Returns a regex for the given uri for matching directories on the file system
     *
     * @param $uri
     * @return string
     */
    protected function createUriRegex($uri) {
        $parts = explode('/', trim($uri, '/'));

        for ($i = 0; $i < count($parts); $i++) {
            $parts[$i] = '([0-9]+-)?' . $parts[$i];
        }

        return '/' . implode('\/', $parts) . '$/';
    }

    /**
     * Returns the uri for a given directory
     *
     * @param string|SplFileInfo $directory
     * @return string
     */
    protected function getUriByDirectory($directory) {

        if ($directory instanceof SplFileInfo) {
            $directory = $directory->getRealPath();
        }

        $directory = Path::makeRelative($directory, $this->path);
        return trim(preg_replace('/(\/|^)([0-9]+-)?/', '/', $directory), '/');
    }

    /**
     * Return the directory path for the given uri
     * 
     * @param $uri
     * @return string
     */
    private function getDirectoryByUri($uri)
    {
        $finder = new Finder();
        $finder->in($this->path)
            ->path($this->createUriRegex($uri))
            ->directories();

        $directories = iterator_to_array($finder);

        return $this->path . '/' . (reset($directories)->getRelativePathname());
    }

    /**
     * Returns all child pages for the given uri
     *
     * @param string $uri
     * @returns PageCollection
     */
    public function findChildrenByUri($uri)
    {
        $path = $this->getDirectoryByUri($uri);
        $finder = new Finder();
        $finder->in($path)->directories();
        $collection = new PageCollection();

        foreach($finder as $directory) {
            $uri = $this->getUriByDirectory($directory);
            $collection->add(new Page($uri, $this->getPageTypeForPath($directory), null, $this));
        }

        return $collection;
    }

    /**
     * Returns the document for the given page
     *
     * @param Page $page
     * @return Document
     */
    public function getDocumentForPage(Page $page)
    {
        $finder = new Finder();
        $path = $this->getDirectoryByUri($page->getUri());

        $finder->in($path)->name($page->getType() . '.*')->depth(0);

        /** @var SplFileInfo $first */
        $first = reset(iterator_to_array($finder->getIterator()));

        return FrontMatter::parse($first->getContents());
    }

    /**
     * Returns page type for given path
     *
     * The page type is defined by the file name of the first found frontmatter file in the given directory
     *
     * @param string|SplFileInfo $path Path to page directory
     * @return string
     */
    protected function getPageTypeForPath($path)
    {
        $finder = new Finder();

        if ($path instanceof SplFileInfo) {
            $path = $path->getRealPath();
        }

        $finder->in($path)->files()->depth(0);

        /** @var SplFileInfo $file */
        foreach($finder as $file) {
            if ($file->openFile()->fread(3) === "---") {
                $content = file_get_contents($file->getRealpath());
                if (FrontMatter::isValid($content)) {
                    return reset(explode('.', $file->getBasename()));
                };
            }
        }

        throw new ContentNotFoundException('No suitable frontmatter file found in ' . $path);
    }
} 