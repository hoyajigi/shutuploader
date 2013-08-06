<?php

final class Me2TagList implements ArrayAccess, IteratorAggregate, Countable {
	protected $tags;

	function __construct(SimpleXMLElement $tags) {
		$this->tags = $tags;
	}

	function contains($tag) {
		$tags = func_get_args();
		return $this->containsAll($tags);
	}

	function containsAll(array $tags) {
		foreach($tags as $tag) {
			settype($tag, 'string');
			$found = false;

			foreach($this->tags->tag as $one) if($tag == (string) $one->name) {
				$found = true;
				break;
			}

			if(!$found)
				return false;
		}

		return true;
	}

	function containsAny(array $tags) {
		foreach($tags as $tag) {
			settype($tag, 'string');
			
			foreach($this->tags->tag as $one) if($tag == (string) $one->name)
				return true;
		}

		return false;
	}

	function toArray() {
		$tags = array();
		foreach($this->tags->tag as $tag)
			$tags[(string) $tag->name] = (string) $tag->url;
		return $tags;
	}

	function offsetGet($name) {
		if(is_string($name)) {
			$name = trim(strtolower($name));
			foreach($this->tags->tag as $tag) if($name == (string) $tag->name)
				return (string) $tag->url;
			return;
		}

		$i = $name < 0 ? count($this) + $name : (int) $name;
		foreach($this->tags->tag as $tag) if($i-- == 0)
			return (string) $tag->name;
	}

	function offsetSet($_, $__) {
		throw new BadMethodCallException;
	}

	function offsetUnset($_) {
		throw new BadMethodCallException;
	}

	function offsetExists($name) {
		if(is_string($name)) {
			$name = trim(strtolower($name));
			foreach($this->tags->tag as $tag) if($name == (string) $tag->name)
				return true;
			return false;
		}

		$i = (int) $name;
		return ($i < 0 ? 1 + $i : $i) < count($this);
	}

	function getIterator() {
		return new ArrayIterator(array_keys($this->toArray()));
	}

	function count() {
		return count($this->tags->tag);
	}

	function __toString() {
		return join(' ', array_keys($this->toArray()));
	}
}

