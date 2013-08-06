<?php
require_once dirname(__FILE__) . '/Api.php';
require_once dirname(__FILE__) . '/Person.php';

class Me2PersonList extends Me2Api
                    implements ArrayAccess, IteratorAggregate, Countable {
    public $method;
    public $parameters;
    public $authenticatedUser;
    protected $list;
    protected $length;

    function __construct(
        $method,
        array $parameters = array(),
        Me2AuthenticatedUser $auth = null
    ) {
        $this->method = $method;
        $this->parameters = $parameters;
        $this->authenticatedUser = $auth;
    }

    protected function getList() {
        if (is_array($this->list)) {
            return;
        }

        $result = self::call(
            $this->method,
            $this->parameters,
            $this->authenticatedUser
        );

        $this->list = array();
        if (count($result->person)) foreach($result->person as $person) {
            $this->list[] = new Me2Person($person);
        }

        $this->length = count($this->list);
    }

    function getIterator() {
        $this->getList();
        return new ArrayIterator($this->list);
    }

    function offsetGet($i) {
        $this->getList();
        return $i < 0 ? $this->list[count($this->list) + $i] : $this->list[$i];
    }

    function offsetSet($_, $__) {
        throw new BadMethodCallException;
    }

    function offsetExists($i) {
        $this->getList();
        return ($i < 0 ? 1 + $i : $i) < count($this->list);
    }

    function offsetUnset($_) {
        throw new BadMethodCallException;
    }

    function count() {
        if (!is_int($this->length)) {
            $this->getList();
        }

        return $this->length;
    }

    function refresh() {
        $this->list = $this->length = null;
    }

    function toArray() {
        $this->getList();
        return $this->list;
    }
}

# vim:set ts=4 sw=4 sts=4 et:
