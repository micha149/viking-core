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
     * @var string
     */
    private $startpage;

    /**
     * @param string $path
     * @param string $startpage
     */
    public function __construct($path, $startpage) {
        $this->path = $path;
        $this->startpage = $startpage;
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
            ->depth(count(explode('/', trim($uri, '/'))) - 1)
            ->directories();

        $directories = iterator_to_array($finder);

        if (count($directories) == 0) {
            throw new ContentNotFoundException('No Page found for uri ' . $uri);
        }

        $directory = reset($directories);

        return new Page($uri, $this->getPageTypeForPath($directory), $this->getPageSortForPath($directory), $this);
    }

    /**
     * Returns a regex for the given uri for matching directories on the file system
     *
     * @param $uri
     * @return string
     */
    protected function createUriRegex($uri) {

        if ($uri === '/') {
            $uri = $this->startpage;
        }

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
        $uri = trim(preg_replace('/(\/|^)([0-9]+-)?/', '/', $directory), '/');

        if ($uri === $this->startpage) {
            return '/';
        }

        return $uri;
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
        return $this->findChildrenByDirectory($path);
    }

    /**
     * Resolves hild pages for a given directory path
     *
     * @param string $path
     * @return PageCollection
     */
    protected function findChildrenByDirectory($path) {
        $finder = new Finder();

        $finder->in($path)->directories()->depth(0);
        $collection = new PageCollection();

        foreach($finder as $directory) {
            $uri = $this->getUriByDirectory($directory);
            $collection->add(new Page($uri, $this->getPageTypeForPath($directory), $this->getPageSortForPath($directory), $this));
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
     * @throws ContentNotFoundException when no frontmatter file was found
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

    /**
     * Returns the sort number for the page at the given path
     *
     * @param string $path
     * @return int|null
     */
    protected function getPageSortForPath($path) {

        preg_match('/(?:([0-9]+)-)?[^\/]+$/', $path, $matches);

        if (!isset($matches[1])) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Returns collection with pages on root level
     *
     * @return PageCollection
     */
    public function findRootPages()
    {
        return $this->findChildrenByDirectory($this->path);
    }
} 