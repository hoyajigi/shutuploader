<?php
require_once dirname(__FILE__) . '/Person.php';
require_once dirname(__FILE__) . '/Post.php';

final class Me2AuthenticatedUser extends Me2Person {
    public $apiKey;
    protected $settings = null;

    function __construct($name, $key, $validate = true) {
        parent::__construct($name);
        $this->apiKey = $key;

        if ($validate) {
            $this->validate(true);
        }
    }

    function authenticate($key) {
        $this->apiKey = $key;
        return $this;
    }

    function validate($throwException = false) {
        try {
            self::call('noop', array(), $this);
            return true;
        } catch (Me2UnauthenticatedUserException $e) {
            if ($throwException) {
                throw $e;
            } else {
                return false;
            }
        }
    }

    function __get($name) {
        switch (strtolower($name)) {
            case 'mytags':
            case 'tags':
                return $this->getMyTags();

            case 'mytagsintab':
            case 'tagsintab':
                return $this->getMyTagsInTab();
        }

        return parent::__get($name);
    }

    function __set($name, $value) {
        if (isset(self::$attr_xml_map[$name])) {
            $name = self::$attr_xml_map[$name];
        }

        if ($name == 'description') {
            self::call(
                "set_description/{$this->name}",
                array('description' => $value),
                $this
            );

            $this->attributes->description = $value;
        }
    }

    protected function getSettings() {
        if (is_null($this->settings)) {
            $this->settings = self::call('get_settings', array(), $this);
        }

        return $this->settings;
    }

    protected function getMyTags() {
        return split('[[:space:]]+', $this->getSettings()->mytags);
    }

    protected function getMyTagsInTab() {
        return split('[[:space:]]+', $this->getSettings()->mytagsInTab);
    }

    function post(
        $post, $tags = '', $icon = 1,
        $attachment = null, $comment = Me2Comment::Opened
    ) {
        return new Me2Post($this, $post, $tags, $icon, $attachment, $comment);
    }

    function comment(Me2Post $post, $comment) {
        return new Me2Comment($this, $post, $comment);
    }

    function metoo(Me2Post $post) {
        $post->metoo($this);
    }
}

class Me2UnauthenticatedUserException extends Me2Exception {}

# vim:set ts=4 sw=4 sts=4 et:
