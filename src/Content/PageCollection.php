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

use Viking\Content\Exception\UnexpectedArgumentException;

/**
 * Class PageCollection
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Content
 */
class PageCollection implements \IteratorAggregate, \ArrayAccess {

    protected $pages = array();

    /**
     * Add a page to the collection
     *
     * @param Page $page
     * @return $this
     */
    public function add(Page $page) {
        $this->pages[] = $page;
        return $this;
    }

    public function filter($callback) {
        $collection = new static();

        foreach ($this as $page) {
            if (call_user_func($callback, $page)) {
                $collection->add($page);
            }
        }

        return $collection;
    }

    public function visible() {
        return $this->filter(function($page) {
            return $page->isVisible();
        });
    }

    public function invisible() {
        return $this->filter(function($page) {
            return $page->isInvisible();
        });
    }

    /**
     * Return the collections iterator
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->pages);
    }

    /**
     * Returns true if a page for the given offset exists
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->pages[$offset]);
    }

    /**
     * Returns the page for the given offset
     *
     * @return Page
     */
    public function offsetGet($offset)
    {
        return $this->pages[$offset];
    }

    /**
     * Sets a page on the given offset
     *
     * @param integer $offset
     * @param Page $page
     */
    public function offsetSet($offset, $page)
    {
        if (!$page instanceof Page) {
            throw new UnexpectedArgumentException('PageCollection only accepts Page items');
        }
        $this->pages[$offset] = $page;
    }

    /**
     * Removes the page on the given offset from the collection
     *
     * @param integer $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->pages[$offset]);
    }
}