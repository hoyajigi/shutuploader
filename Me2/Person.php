<?php
require_once dirname(__FILE__) . '/Entity.php';
require_once dirname(__FILE__) . '/FriendList.php';
require_once dirname(__FILE__) . '/PostList.php';
require_once dirname(__FILE__) . '/RecentPostList.php';

class Me2Person extends Me2Entity {
    public $name;
    public $posts;

    protected static $attr_xml_map = array(
        'openId' => 'openid',
        'openID' => 'openid',
        'nick' => 'nickname',
        'nickName' => 'nickname',
        'screenname' => 'nickname',
        'screenName' => 'nickname',
        'profileImage' => 'face',
        'signature' => 'description',
        'rss' => 'rssDaily',
        'feed' => 'rssDaily'
    );

    protected $friends = null;
    protected $attributes = null;

    function __construct($name, $checkExistance = false) {
        if ($name instanceof SimpleXMLElement) {
            $this->attributes = $name;
            $name = (string) $name->id;
        }

        if (!eregi('^[-a-z0-9_]{3,}$', $name = trim($name))) {
            throw new UnexpectedValueException(
                'Name should be least 3 characters of '.
                'roman alphabets and digits and dashes(-, _)'
            );
        }

        $this->name = strtolower(trim($name));
        $checkExistance and $this->retrieve();
        $this->posts = new Me2PostList($this);
        $this->latestPosts = $this->recentPosts = new Me2RecentPostList($this);
    }

    protected function retrieve() {
        $this->attributes = self::call("get_person/{$this->name}");
    }

    function authenticate($key) {
        return new Me2AuthenticatedUser($this->name, $key, true);
    }

    function isAuthorOf(Me2Entity $entity) {
        if ($entity instanceof Me2Post || $entity instanceof Me2Comment) {
            return $entity->author->name == $this->name;
        } else if ($entity instanceof self) {
            return $entity->name == $this->name;
        }

        return false;
    }

    function __get($name) {
        if (!$this->attributes) {
            $this->retrieve();
        }

        switch ($name) {
            case 'url':
            case 'me2dayHome':
                return "http://me2day.net/{$this->name}";

            case 'friends':
                return is_null($this->friends) ?
                       $this->friends = new Me2FriendList(
                           $this,
                           $this->attributes->friendsCount
                       ) : $this->friends;

            case 'updatedAt':
                return new DateTime($this->attributes->updated);

            case 'inviter':
                return new self((string) $this->attributes->invitedBy);

            default:
                if (isset(self::$attr_xml_map[$name])) {
                    return (string) $this->attributes->{
                        self::$attr_xml_map[$name]
                    };
                } else if (isset($this->attributes->$name)) {
                    return (string) $this->attributes->$name;
                }
        }
    }

    function __toString() {
        return $this->name;
    }
}

# vim:set ts=4 sw=4 sts=4 et:
