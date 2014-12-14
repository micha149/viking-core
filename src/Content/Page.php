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
use Viking\Content\Exception\ContentNotFoundException;
use Viking\Content\Exception\UnexpectedDocumentAccessException;

/**
 * Page Class
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Content
 */
class Page implements \ArrayAccess {

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var integer|null
     */
    protected $sort = null;

    /**
     * @var PageRepository
     */
    private $repository;

    /**
     * @var Document
     */
    protected $document;

    /**
     * @param string $uri
     * @param string $type
     * @param integer|null $sort
     * @param PageRepository $repository
     */
    public function __construct($uri, $type, $sort = null, PageRepository $repository) {
        $this->uri = $uri;
        $this->type = $type;
        $this->sort = $sort;
        $this->repository = $repository;
    }

    /**
     * Returns the sort value of this page
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Sets the sort value of this page
     * @param int $sort
     * @return Page $this
     */
    public function setSort($sort)
    {
        $this->sort = (int) $sort;
        return $this;
    }

    /**
     * Return true if the page is invisible
     *
     * All pages without a sort value are invisible by definition.
     *
     * @return bool
     */
    public function isInvisible() {
        return $this->sort === null;
    }

    /**
     * Return true if the page is visible
     *
     * All pages with a sort value are visible by definition.
     *
     * @return bool
     */
    public function isVisible() {
        return !$this->isInvisible();
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

    /**
     * Returns the pages type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Page
     */
    public function getParent() {
        $parentUriParts = explode("/", $this->uri);
        array_pop($parentUriParts);
        return $this->repository->findOneByUri(implode("/", $parentUriParts));
    }

    public function getChildren() {
        return $this->repository->findChildrenByUri($this->uri);
    }

    /**
     * Returns content document of this page
     *
     * @return Document
     */
    public function getDocument() {
        if (!$this->document) {
            $this->document = $this->repository->getDocumentForPage($this);
        }
        return $this->document;
    }

    /**
     * Finds subpages by relative uri. This methods accepts one or more arguments.
     * If one argument is passed, a Page insance will be returned, a PageCollection will
     * be returned for more than one uris.
     *
     * @param string $uri ... One or more uris to resolve
     * @throws ContentNotFoundException If a single uri is given and no page is found
     * @return Page|PageCollection
     */
    public function find($uri)
    {
        $args = func_get_args();
        $collection = new PageCollection();

        foreach ($args as $uri) {
            try {
                $page = $this->repository->findOneByUri(str_replace('//', '/', $this->getUri() . '/' .  $uri));
                $collection->add($page);
            } catch (ContentNotFoundException $e) {
                if (count($args) === 1) {
                    throw $e;
                }
            }
        }

        if (count($args) === 1) {
            return $collection[0];
        }

        return $collection;
    }

    /**
     * Checks if the given offset is available in the pages document.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        $document = $this->getDocument();
        return isset($document[$offset]);
    }

    /**
     * Returns the given offset from the pages document
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $document = $this->getDocument();
        return $document[$offset];
    }

    /**
     * Required by array access interfaces, but not implemented/supported
     */
    public function offsetSet($offset, $value)
    {
        throw new UnexpectedDocumentAccessException('Setting a document value on a page is not permitted');
    }

    /**
     * Required by array access interfaces, but not implemented/supported
     */
    public function offsetUnset($offset)
    {
        throw new UnexpectedDocumentAccessException('Unsetting a document value on a page is not permitted');
    }
}