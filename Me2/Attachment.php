<?php

abstract class Me2Attachment {
    var $type = null;
}

final class Me2Image extends Me2Attachment {
    const MaxSize = 10485760;

    var $name;
    var $mime = 'image/jpeg';

    static function fromFile($path, $name = null) {
        if (is_null($name)) $name = basename($path);
        $info = getimagesize($path);

        switch ($info['mime']) {
            case 'image/jpeg':
                return new self(join('', file($path)), $name);

            case 'image/png': $gd = 'imagecreatefrompng'; break;
            case 'image/gif': $gd = 'imagecreatefromgif'; break;

            default: throw new UnexpectedValueException(
                "JPEG, PNG, GIF 형식의 파일만 지원합니다."
            );
        }
        return self::fromGD($gd($path), $name);
    }

    static function fromFilePointer($fp, $name = null) {
        $binary = '';
        do {
            $binary .= fgets($fp);
        } while (!feof($fp));

        return self::fromGD(imagecreatefromstring($binary));
    }

    static function fromURL($url, $name = null) {
        throw new Exception('Me2Image::fromURL is not implemented yet!');
    }

    static function fromGD($image, $name = null) {
        ob_start();
        imagejpeg($image, null, 100);
        return new self(ob_get_clean(), $name);
    }

    function __construct($binary, $name = null) {
        if (strlen($binary) > self::MaxSize) {
            throw new UnexpectedValueException(
                self::MaxSize . "Bytes 이하의 파일만 지원합니다."
            );
        }

        if (is_null($name)) $name = md5(rand()).'.jpg';

        $this->binary = $binary;
        $this->name = $name;
    }

    function __toString() {
        return (string) $this->binary;
    }
}

abstract class Me2Callback extends Me2Attachment {
    function __construct($url, $icon) {
        $this->url = $url;
        $this->icon = $icon;
    }

    function __toString() {
        return $this->url;
    }
}

final class Me2CallbackDocument extends Me2Callback {
    var $type = 'document';
}

final class Me2CallbackPhoto extends Me2Callback {
    var $type = 'photo';
}

final class Me2CallbackVideo extends Me2Callback {
    var $type = 'video';
}

final class Me2CallbackAudio extends Me2Callback {
    var $type = 'audio';
}

