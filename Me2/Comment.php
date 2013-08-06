<?php
require_once dirname(__FILE__) . '/Entity.php';
require_once dirname(__FILE__) . '/Post.php';
require_once dirname(__FILE__) . '/Person.php';
require_once dirname(__FILE__) . '/AuthenticatedUser.php';

final class Me2Comment extends Me2Entity {
    const Allow = 0;
    const Allowed = 0;
    const Open = 0;
    const Opened = 0;
    const ReceiveSMS = 1;
    const ReceiveSms = 1;
    const Close = 2;
    const Closed = 2;
    const Deny = 2;
    const Denied = 2;

    function __construct(Me2Person $author, Me2Post $post, $comment) {
        if ($comment instanceof SimpleXMLElement) {
            $this->id = $comment->commentId;
            $this->content = (string) $comment->body;
            $this->createdAt = new DateTime($comment->pubDate);
        }
        else {
            if (!($author instanceof Me2AuthenticatedUser)) {
                throw new Me2UnauthenticatedUserException(
                    'Need to authenticate'
                );
            }

            $this->content = trim($comment);
            $this->createdAt = new DateTime();

            $result = self::call('create_comment', array(
                'post_id' => $post->url,
                'body' => $this->content
            ), $author);

            if (!$result || $result->code != '0') {
                throw new Me2Exception($result->message);
            }

            $post->comments->reset(1);
            $this->id = $result->commentId;
        }

        $this->author = $author;
        $this->post = $post;
        $this->body = $this->content;
        $this->publishedAt = $this->postedAt = $this->createdAt;
    }

    protected function _() {
        if (is_null($this->id)) {
            throw new Me2DeletedCommentException;
        }

        return $this;
    }

    function delete(Me2AuthenticatedUser $user) {
        if (!$user->isAuthorOf($this->_()->post)) {
            throw new Me2Exception(
                "The user($user) has no authority to delete this comment"
            );
        }

        $result = self::call(
            'delete_comment',
            array('comment_id' => $this->id),
            $user
        );

        if (!$result || $result->code != '0') {
            throw new Me2Exception($result->message);
        }

        $this->post->comments->reset(-1);

        $this->id = $this->body = $this->content = $this->post
                  = $this->postedAt = $this->publishedAt = $this->createdAt
                  = $this->author = null;
    }

    function __toString() {
        return $this->_()->body;
    }
}

final class Me2DeletedCommentException extends Me2Exception {}

# vim:set ts=4 sw=4 sts=4 et:
