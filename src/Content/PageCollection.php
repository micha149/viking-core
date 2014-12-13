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

    protected $pages;

    public function __construct(array $pages = array()) {
        $this->pages = $pages;
    }

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

    /**
     * Returns a new collection with pages of the current collection which pass a truth test
     *
     * @param Callable $callback
     * @return PageCollection
     */
    public function filter($callback) {
        $collection = new static();

        foreach ($this as $page) {
            if (call_user_func($callback, $page)) {
                $collection->add($page);
            }
        }

        return $collection;
    }

    /**
     * Returns a new collection with all pages of the current collection where the given field matches
     * the given value. With the operator parameter we can define how the value of the field have to match.
     * For example, all pages where the field "Title" begins with "Project" can be selected with the following
     * call:
     *
     *    $pages->filterBy('Title', '^=', 'Project'); // All pages where "Title" begins with "Project"
     *
     * Supported operators:
     *
     * - Prefix operator `|=`
     *   This is for field with hyphen seperated values, like slugs. It matches when the field begins with
     *   the given value, followed by a hyphen (-)
     *
     * - Field contains operator `*=`
     *   Matches pages where the field contains the given substring.
     *
     * - Field contains word operator `~=`
     *   Matches pages where the field containing the given word, delimited by spaces.
     *
     * - Field ends with operator `$=`
     *   Matches pages where the field end with the given value
     *
     * - Field equals operator `==`
     *   Matches pages where the field is exactly the given value
     *
     * - Field not equals operator `!=`
     *   Matches pages where the field is not equal the given value
     *
     * - Field starts with operator `^=`
     *   Matches pages where the field starts with the given value
     *
     * @param string $field Field name
     * @param string $operator The operator to check values. `==` when omitted.
     * @param string $value The value to match
     * @param bool $incasesensitive Case must not match
     * @param bool $inverse True to get all pages which didn't match
     * @throws UnexpectedArgumentException when a unexpected operator was given
     * @return PageCollection
     */
    public function filterBy($field, $operator, $value = null, $incasesensitive = false, $inverse = false) {

        if (!$value) {
            $value = $operator;
            $operator = '==';
        }

        switch($operator) {
            case "!=":
                $inverse = !$inverse;
            case "==":
                $pattern = "/^$value$/";
                break;
            case "|=":
                $pattern = "/$value-/";
                break;
            case "*=":
                $pattern = "/$value/";
                break;
            case "~=":
                $pattern = "/(^|\\s)$value($|\\s)/";
                break;
            case "$=":
                $pattern = "/".$value."$/";
                break;
            case "^=":
                $pattern = "/^".$value."/";
                break;
            default:
                throw new UnexpectedArgumentException('Unknown filter operator "' . $operator . '"');
        }

        if ($incasesensitive) {
            $pattern .= 'i';
        }

        return $this->filter(function($page) use ($field, $pattern, $inverse) {
            return $inverse != preg_match($pattern, trim($page[$field]));
        });
    }

    /**
     * Returns a new page collection with all visible pages of the current collection
     *
     * @return PageCollection
     */
    public function visible() {
        return $this->filter(function($page) {
            return $page->isVisible();
        });
    }

    /**
     * Returns a new page collection with all invisible pages of the current collection
     *
     * @return PageCollection
     */
    public function invisible() {
        return $this->filter(function($page) {
            return $page->isInvisible();
        });
    }

    /**
     * Returns a new collection with children of pages in the current collection
     *
     * @return PageCollection
     */
    public function children() {
        $collection = new static();

        /** @var $page Page */
        foreach($this->pages as $page) {
            $collection = $collection->merge($page->getChildren());
        }

        return $collection;
    }

    /**
     * Returns an array with all pages of the current collection
     *
     * @return array
     */
    public function toArray() {
        return $this->pages;
    }

    /**
     * Returns a new page collection with pages of the current and the given collections
     *
     * @param PageCollection $collection
     * @return PageCollection
     */
    public function merge(PageCollection $collection) {
        return new static(array_merge($this->toArray(), $collection->toArray()));
    }

    /**
     * Returns the number of pages in the current collection
     *
     * @return int
     */
    public function count() {
        return count($this->pages);
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
     * @param integer $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->pages[$offset]);
    }

    /**
     * Returns the page for the given offset
     *
     * @param integer $offset
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
     * @throws Exception\UnexpectedArgumentException
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