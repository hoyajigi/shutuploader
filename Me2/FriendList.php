<?php
require_once dirname(__FILE__) . '/Person.php';
require_once dirname(__FILE__) . '/AuthenticatedUser.php';
require_once dirname(__FILE__) . '/PersonList.php';
require_once dirname(__FILE__) . '/TaggedPersonList.php';

final class Me2FriendList extends Me2PersonList {
    public $whose, $close, $family;
    protected $mytag, $supporter;

    function __construct(Me2Person $whose, $count = null) {
        parent::__construct($method = "get_friends/$whose->name");

        $this->whose = $whose;
        $this->length = (int) $count;

        $this->close = new Me2PersonList($method, array('scope' => 'close'));

        $this->family = $this->families = new Me2PersonList(
            $method,
            array('scope' => 'family')
        );
    }

    function __get($name) {
        $auth = $this->whose instanceof Me2AuthenticatedUser;

        if (eregi('^(my)?tags?$', $name)) {
            if ($auth) {
                if (empty($this->mytag)) {
                    $this->mytag = new Me2TaggedPersonList($this->whose);
                }

                return $this->mytag;
            }

            throw new Me2UnauthenticatedUserException(
                'Need to authenticate for getting tagged friends'
            );
        } else if (eregi('^supporters?', $name)) {
            if ($auth) {
                if (empty($this->supporter)) {
                    $this->supporter = new Me2PersonList(
                        $this->method,
                        array('scope' => 'supporter'),
                        $this->whose
                    );
                }

                return $this->supporter;
            }

            throw new Me2UnauthenticatedUserException(
                'Need to authenticate for getting supporters'
            );
        }
    }
}

# vim:set ts=4 sw=4 sts=4 et:
