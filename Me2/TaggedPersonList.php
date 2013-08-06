<?php
require_once dirname(__FILE__) . '/Person.php';
require_once dirname(__FILE__) . '/AuthenticatedUser.php';
require_once dirname(__FILE__) . '/PersonList.php';

final class Me2TaggedPersonList implements ArrayAccess {
	public $whose;

	function __construct(Me2AuthenticatedUser $whose) {
		$this->whose = $whose;
	}

	function offsetGet($tag) {
		if(!ereg("[^?/#&;.,<>+ \t\r\n]+", $tag, $groups))
			return array();
		$tag = $groups[0];

		return new Me2PersonList(
			"get_friends/{$this->whose->name}",
			array('scope' => "mytag[$tag]"),
			$this->whose
		);
	}

	function offsetSet($_, $__) {
		throw new BadMethodCallException;
	}

	function offsetExists($i) {
		return true;
	}

	function offsetUnset($_) {
		throw new BadMethodCallException;
	}
}

