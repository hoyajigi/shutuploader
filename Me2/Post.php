<?php
require_once dirname(__FILE__) . '/Entity.php';
require_once dirname(__FILE__) . '/Person.php';
require_once dirname(__FILE__) . '/Comment.php';
require_once dirname(__FILE__) . '/TagList.php';
require_once dirname(__FILE__) . '/CommentList.php';
require_once dirname(__FILE__) . '/Attachment.php';

final class Me2Post extends Me2Entity {
    const UrlPattern = '{^
        (?: http:// )? (?: www\. )? me2day\.net /
        (?P<name> [^/]{3,} ) / (?P<date> \d{4,} / \d{2} / \d{2}) /?
        [#] (?P<time> \d{2} : \d{2} : \d{2})
    $}ix';

    const LinkPattern = '{
            ("([^"]+)":([^ ]+))
        |    (/([-a-z0-9_]{3,12})/)
    }ix';

    protected $attributes;

    public $author, $body, $comments, $tags;

    protected static $attr_xml_map = array(
        'url' => 'permalink',
        'type' => 'kind',
        'iconUrl' => 'icon',
        'metoo' => 'metooCount'
    );

    protected static $tidy, $tidyConfig = array(
        'output-xhtml' => true,
        'wrap' => 0,
        'drop-empty-paras' => true,
        'drop-font-tags' => true,
        'drop-proprietary-attributes' => true,
        'logical-emphasis' => true,
        'lower-literals' => true
    );

    static function fromUrl($url) {
        if (!preg_match(self::UrlPattern, $url, $match)) {
            throw new InvalidArgumentException(
                "$url is not correct me2day post url"
            );
        }

        $posts = self::call('get_posts', array('post_id' => $url));

        if (count($posts->post)) {
            return new self(
                new Me2Person($posts->post->author),
                $posts->post
            );
        }
    }

    protected static function previewReplacer($g) {
        if (isset($g[4])) {
            try {
                $person = new Me2Person($g[5], true);
                $text = $person->nick;
                $href = $person->url;
            } catch (Exception $e) {
                $classes = array('UnexpectedValueException', 'Me2Exception'); 

                if (in_array(get_class($e), $classes)) {
                    $text = $g[4];
                } else {
                    throw $e;
                }
            }
        } else {
            $text = $g[2];
            $href = $g[3];
        }

        return '<a href="' . htmlspecialchars($href) . '">'
             . htmlspecialchars($text) . '</a>';
    }

    static function preview($text) {
        return '<p>' . preg_replace_callback(
            self::LinkPattern,
            array(__CLASS__, 'previewReplacer'), $text
        ) . '</p>';
    }

    function __construct(
        Me2Person $author, $body, $tags = '', $icon = 1,
        Me2Attachment $attachment = null, $comments = Me2Comment::Opened
    ) {
        if ($body instanceof SimpleXMLElement) {
            $this->attributes = $body;
        } else {
            if (!($author instanceof Me2AuthenticatedUser)) {
                throw new Me2UnauthentiactedUserException(
                    'Need to authenticate'
                );
            }

            settype($icon, 'integer');
            settype($comments, 'integer');

            if ($icon < 1 || $icon > 12) {
                throw new RangeException(
                    'The icon number must be between 1 and 12'
                );
            }

            $bool = array(true => 'true', false => 'false');

            $params = array(
                'post[body]' => (string) $body,
                'post[tags]' => trim(join(' ', (array) $tags)),
                'post[icon]' => $icon,
                'receive_sms' => $bool[$comments === Me2Comment::ReceiveSMS],
                'close_comment' => $bool[$comments === Me2Comment::Closed]
            );

            $http_method = Me2Api::HttpGet;

            if ($attachment instanceof Me2Attachment) {
                if ($attachment instanceof Me2Image) {
                    $param = 'attachment';
                    $http_method = Me2Api::HttpPost;
                } else {
                    $param = 'callback_url';
                    $params['icon_url'] = $attachment->icon;
                    $params['content_type'] = $attachment->type;
                }
                $params[$param] = $attachment;
            }

            $this->attributes = self::call(
                "create_post/{$author->name}", $params, $author, $http_method
            );
        }

        $this->author = $author;
        $this->tags = new Me2TagList($this->attributes->tags);
        $this->comments = new Me2CommentList(
            $this,
            (int) $this->attributes->commentsCount
        );

        $body = (string) $this->attributes->body;

        if (extension_loaded('tidy') && function_exists('tidy_clean_repair')) {
            if (is_null(self::$tidy)) {
                self::$tidy = new tidy;
            }

            self::$tidy->parseString($body, self::$tidyConfig, 'utf8');
            self::$tidy->cleanRepair();

            $body = preg_replace(
                '(^\s*<body>\s*|\s*</body>\s*$)', '',
                self::$tidy->body()
            );
        }

        $this->body = $this->content = $body;
    }

    function __get($name) {
        switch($name) {
            case 'createdAt':
            case 'publishedAt':
            case 'postedAt':
                return new DateTime($this->attributes->pubDate);

            case 'commentClosed':
                return $this->attributes->commentClosed == 'true';

            case 'commentable':
                return $this->attributes->commentClosed == 'false';

            case 'authorNick':
            case 'authorNickName':
            case 'authorNickname':
            case 'authorScreenName':
            case 'authorScreenname':
                return (string) $this->attributes->author->nickname;

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

    function comment(Me2AuthenticatedUser $author, $comment) {
        return new Me2Comment($author, $this, $comment);
    }

    function metoo(Me2AuthenticatedUser $author) {
        if ($author->name === $this->author->name) {
            throw new Me2MetooByOneselfException;
        }

        self::call('metoo', array('post_id' => $this->url), $author);
        $this->attributes->metooCount = $this->attributes->metooCount + 1;
    }

    function __toString() {
        return $this->body;
    }
}

class Me2MetooByOneselfException extends Me2Exception {}

# vim:set ts=4 sw=4 sts=4 et:
