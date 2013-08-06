<?php
require_once dirname(__FILE__) . '/Comment.php';
require_once dirname(__FILE__) . '/Post.php';
require_once dirname(__FILE__) . '/Person.php';

final class Me2CommentList
    extends Me2API implements IteratorAggregate, ArrayAccess, Countable {

    protected $list;
    protected $length;

    function __construct(Me2Post $post, $length = null) {
        $this->post = $post;
        $this->length = $length;
    }

    function reset($add = null) {
        $this->list = null;

        if (is_int($add) && !is_null($this->length)) {
            $this->length += $add;
        }
    }

    function refresh() {
        $this->length = null;
        $this->list = array();

        $list = self::call(
            'get_comments',
            array('post_id' => $this->post->url)
        );

        foreach ($list->comment as $comment) {
            $this->list[] = new Me2Comment(
                new Me2Person((string) $comment->author->id),
                $this->post,
                $comment
            );
        }
    }

    function toArray() {
        if (!is_array($this->list)) {
            $this->refresh();
        }

        return $this->list;
    }

    function getIterator() {
        return new ArrayIterator($this->toArray());
    }

    function offsetGet($i) {
        $list = $this->toArray();
        return $list[$i < 0 ? count($list) + $i : $i];
    }

    function offsetSet($_, $__) {
        throw new BadMethodCallException;
    }

    function offsetUnset($_) {
        throw new BadMethodCallException;
    }

    function offsetExists($i) {
        return ($i < 0 ? 1 + $i : $i) < count($this);
    }

    function count() {
        return is_null($this->length) ? count($this->toArray()) : $this->length;
    }
}

# vim:set ts=4 sw=4 sts=4 et:
