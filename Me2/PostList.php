<?php
require_once dirname(__FILE__) . '/BasePostList.php';

final class Me2PostList extends Me2BasePostList {
    const RemoteMethod = 'get_posts';
    const DateTimeFormat = 'c';

    public $tag;
    public $from;
    public $to;

    function __construct(Me2Person $whose, $tag=null,
                         DateTime $from=null, DateTime $to=null,
                         int $offset=null, int $limit=null) {
        parent::__construct($whose);

        $this->tag = trim($tag) ? trim(join(' ', (array) $tag)) : null;
        $this->from = $from;
        $this->to = $to;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    function refresh() {
        $from = $this->from ? self::makeDateTime($this->from) : null;
        $to = $this->to ? self::makeDateTime($this->to) : null;
        $offset = (int) $this->offset;
        $limit = !is_null($this->limit) ? (int) $this->limit : null;

        $params = array('offset' => $offset);

        if ($to instanceof DateTime and $to > $this->whose->updatedAt) {
            $to = null;
        }

        if ($from instanceof DateTime and is_null($to)) {
            $to = clone $this->whose->updatedAt;
            $to->modify('+1second');
        } else if ($to instanceof DateTime and is_null($from)) {
            $from = clone $to;
            $from->modify('-1day');
        }

        if(is_string($this->tag) && trim($this->tag)) {
            $this->tag = trim($this->tag);
            $tags = split('[[:space:]]+', $this->tag);

            if(count($tags))
                $params['scope'] = 'tag[' . $tags[0] . ']';
        }
        else
            $tags = array();

        if ($from instanceof DateTime) {
            $params['from'] = $from->format(self::DateTimeFormat);
            $interval = true;
        }

        $max_limit = 100;
        $max_days = 7;
        $limited = !is_null($limit);

        $counts = array(0);
        while (true) {
            if ($interval) {
                $params['to'] = $to->format(self::DateTimeFormat);
            }

            if (!$limited or $limit > $max_limit) {
                $params['count'] = $max_limit;
            } else {
                $params['count'] = $limit;
            }

            parent::refresh($params, true);
            $counts[] = count($this->list);
            $i = count($counts) - 1;
            $current_count = ($count = $counts[$i]) - $counts[$i - 1];

            if ($limited) {
                if ($limit <= $current_count) break;
                $limit -= $current_count;
            }

            if ($interval) {
                $days = ceil(
                    ($to->format('U') - $from->format('U')) / (60 * 60 * 24)
                );
                if ($days > $max_days) {
                    $to->modify("-{$max_days}days");
                } else {
                    break;
                }
            } else if (!$limited) {
                if ($current_count < $max_limit) break;
                else $params['offset'] += $max_limit;
            }
        }

        # 복수 태그 선택시
        if(count($tags) > 1) {
            $list = array();

            foreach($this->list as $i => $post) {
                $includes = true;

                foreach(array_slice($tags, 1) as $t) {
                    if(!isset($post->tags[$t])) {
                        $includes = false;
                        break;
                    }
                }

                if($includes)
                    $list[] = $this->list[$i];
            }

            $this->list = $list;
        }
    }

    protected static function makeDateTime($polymorphic) {
        $result = is_int($polymorphic)
            ? new DateTime("@$polymorphic")
            : (is_string($polymorphic) ? new DateTime($polymorphic) : $polymorphic);

        if($result instanceof DateTime)
            return $result;
        $type = is_object($result) ? get_class($result) : gettype($result);
        throw new UnexpectedValueException("Expected a DateTime instance, but $type found");
    }

    function from($from) {
        if($this->from)
            throw new BadMethodCallException('The from member was setted already');

        $list = clone $this;
        $list->from = self::makeDateTime($from);
        return $list;
    }

    function to($to) {
        if($this->to)
            throw new BadMethodCallException('The from member was setted already');

        $list = clone $this;
        $list->to = self::makeDateTime($to);
        return $list;
    }

    function offset($offset) {
        if($this->offset)
            throw new BadMethodCallException('The offset member was setted already');

        $list = clone $this;
        $list->offset = $offset;
        return $list;
    }

    function limit($limit) {
        if($this->limit)
            throw new BadMethodCallException('The count member was setted already');

        $list = clone $this;
        $list->limit = $limit;
        return $list;
    }

    function between($from, $to) {
        return $this->from($from)->to($to);
    }

    function offsetGet($tag_or_offset) {
        if(is_string($tag_or_offset) || is_array($tag_or_offset)) {
            $tag = $tag_or_offset;
            $list = clone $this;
            $list->tag .= ' ' . (is_array($tag) ? join(' ', $tag) : trim($tag));
            return $list;
        }

        return parent::offsetGet($tag_or_offset);
    }

    function offsetExists($i) {
        if(is_string($i))
            return true;
        return parent::offsetExists($i);
    }
}

