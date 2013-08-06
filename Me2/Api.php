<?php

abstract class Me2Api {
    const Host = 'me2day.net';
    const Port = 80;

    const HttpGet = 'GET';
    const HttpPost = 'POST';
    const HttpPut = 'PUT';
    const HttpDelete = 'DELETE';

    static $debugLogger = null;
    static $applicationKey = null;
    protected static $socket = null;
    protected static $cache = array();

    protected static function generateNonce() {
        $nonce = '';
        for ($i = 0; $i < 8; ++$i) {
            $nonce .= dechex(rand(0, 15));
        }
        return $nonce;
    }

    static function call($method, array $parameters = array(),
                                   Me2AuthenticatedUser $auth = null,
                                   $http_method = self::HttpGet) {
echo $method."<br />";
        $query = self::buildQuery($parameters);
        if ($query) {
            $method .= '.xml';
            if ($http_method == self::HttpGet) $method .= "?$query";
        }

        if (isset(self::$cache[$method])) {
            return self::$cache[$method];
        }

        self::openSocket();

        if (!self::$applicationKey) {
            throw new Me2ApplicationKeyException("Missing application key");
        }

        $headers = array(
            'Host' => self::Host,
            'me2_application_key' => self::$applicationKey
        );

        if ($auth) {
            $nonce = self::generateNonce();
            $_auth = 'Basic ' . base64_encode(
                         "{$auth->name}:$nonce" . md5($nonce . $auth->apiKey)
                     );
            $headers['Authorization'] = $_auth;
        }

        $crlf = "\r\n";

        if ($http_method == self::HttpPost) {
            $attached = true;
            
            $content_type = $attached ? 'multipart/form-data'
                          : 'application/x-www-form-urlencoded';
            $content_disposition = array_pop(explode('/', $content_type));
            $nonce = self::generateNonce();
            $boundary = "--------------------------------$nonce";
            $contents = '';

            foreach ($parameters as $param => $value) {
                $contents .= '--'.$boundary.$crlf;
                $contents .= "Content-Disposition: $content_disposition"
                          .  "; name=\"$param\"";

                if ($value instanceof att) {
                    $name = $value->name;
                    $mime = $value->mime;

                    $contents .= "; filename=\"$name\"$crlf"
                              .  "Content-Transfer-Encoding: binary$crlf"
                              .  "Content-Type: $mime$crlf";
                } else {
                    $contents .= $crlf;
                }
                $contents .= $crlf . $value . $crlf;
            }
            $contents .= "--$boundary$crlf";

            $headers['Content-Type'] = "$content_type; boundary=$boundary";
            $headers['Content-Length'] = strlen($contents);
        } else {
            $contents = '';
        }

        $headers['Connection'] = 'Keep-Alive';
        $request = "$http_method /api/$method HTTP/1.1$crlf";
        foreach ($headers as $equiv => $content) {
            $request .= "$equiv: $content$crlf";
        }
        $request .= $crlf.$contents;
//echo $request;
//        self::log($request);
//       fwrite(self::$socket, $request);
        $length=strlen($request);
        for ($written = 0; $written <$length ; $written += $fwrite) {
     		   echo ($written/$length*100)."%<br />";
     		   flush();
    		   	 $fwrite = fwrite(self::$socket, substr($request, $written,100000));
        		if(!$fwrite){
        			echo "업로드 중 오류 발생";
        			exit();
        			}
         }
    
        
        
//if($method!="noop")
//exit();

        if (!self::log($response = rtrim(fgets(self::$socket)))) {
            self::closeSocket();
            return self::call($method, $parameters, $auth);
        }
//echo $response;
//exit();

        list($httpVer, $code, $status) = split(' +', $response);

        if ($code == 401) {
            throw new Me2UnauthenticatedUserException(
                'Unauthenticated error' .
                ($auth ? ": {$auth->name} with key {$auth->apiKey}" : '')
            );
        } else if ($code == 404) {
            return false;
        }

        $rawResponseString = '';
        $headers = array();

        while (strlen(trim($line = fgets(self::$socket)))) {
            $rawResponseString    .= $line;
            list($equiv, $content) = split(' *: *', rtrim($line));
            $headers[strtolower($equiv)] = $content;
        }

        $length = (int) $headers['content-length'];
        $body   = '';

        while (strlen($body) < $length) {
            $body .= fgets(self::$socket, 128);
        }
//echo $body;
//        self::log($rawResponseString . $body);
        $result = simplexml_load_string(self::log($body));

        if ($code == 500 && $result->code == 1012) {
            throw new Me2ApplicationKeyException($result->message);
        } else if ($code != 200) {
//            throw new Me2Exception($result->message);
        }

        if (ereg('/1\.0$', $httpVer) ||
            eregi('close', $headers['connection'])) {
            self::closeSocket();
        }

        return self::$cache[$method] = $result;
    }

    protected static function openSocket() {
        while (!self::$socket) {
            self::$socket = fsockopen(self::Host, self::Port);
        }
    }

    protected static function closeSocket() {
        if (is_null(self::$socket)) {
            return;
        }

        fclose(self::$socket);
        self::$socket = null;
    }

    protected static function buildQuery($parameters) {
        $result = array();
        foreach ($parameters as $key => $value)
            $result[] = "$key=" . urlencode($value);
        return join('&', $result);
    }

    protected static function log($message) {
        # Phunctional 지원
        if (interface_exists('Callable') && function_exists('Functor')) {
            Functor(self::$debugLogger)->call($message);
        } else if (self::$debugLogger) {
            call_user_func(self::$debugLogger, $message);
        }

        return $message;
    }
}

class Me2Exception extends Exception {}
class Me2ApplicationKeyException extends Me2Exception {}

require_once dirname(__FILE__) . '/AuthenticatedUser.php';

# vim:set ts=4 sw=4 sts=4 et:
