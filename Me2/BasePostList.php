<?php
require_once dirname(__FILE__) . '/Api.php';
require_once dirname(__FILE__) . '/Post.php';

abstract class Me2BasePostList
    extends Me2Api implements ArrayAccess, IteratorAggregate, Countable {

    public $whose;
    protected $list;

    function __construct(Me2Person $whose, $tag = null,
                         DateTime $from = null, DateTime $to = null) {
        $this->whose = $whose;
    }

    final function __clone() {
        $this->list = null;
    }

    function refresh(array $params=array(), $accumulation=false) {
        if (!defined(get_class($this) . '::RemoteMethod')) {
            throw new Exception(
                get_class($this) .  '::RemoteMethod constant must be defined.'
            );
        }

        $remoteMethod = constant(get_class($this) . '::RemoteMethod');

        if (!$accumulation) $this->list = array();
        $list = self::call("$remoteMethod/{$this->whose->name}", $params);

        foreach ($list->post as $post) {
            $this->list[] = new Me2Post($this->whose, $post);
        }
    }

    final function toArray() {
        if (!is_array($this->list)) {
            $this->refresh();
        }

        return count($this->list) ? $this->list : array();
    }

    function offsetGet($i) {
        $list = $this->toArray();
        return $list[$i < 0 ? count($list) + $i : $i];
    }

    final function offsetSet($_, $__) {
        throw new BadMethodCallException;
    }

    final function offsetUnset($_) {
        throw new BadMethodCallException;
    }

    function offsetExists($i) {
        return ($i < 0 ? 1 + $i : $i) < count($this);
    }

    final function getIterator() {
        return new ArrayIterator($this->toArray());
    }

    final function count() {
        return count($this->toArray());
    }
}

# vim:set ts=4 sw=4 sts=4 et:
