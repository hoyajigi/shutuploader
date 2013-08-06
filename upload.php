<?
//echo exec("pwd");
//exit();
require_once 'Me2.php';
Me2Api::$applicationKey = '';

function upload($me2id,$me2key,$body,$tags,$filename)
{

}

function movefile($filename)
{
	$name = md5(rand()).'.mp3';
	$destination="/var/me2toy/shutuploader/upload/".$name;
	move_uploaded_file ($filename ,$destination);
	return $destination;
}

function convert_file($filename,$bitrate=16)
{

//	echo "mplayer -vo null -vc dummy -af resample=44100 -ao pcm:waveheader ".$filename;
//	exit();
//	echo shell_exec("mplayer -vo null -vc dummy -af resample=44100 -ao pcm:waveheader ".$filename);
	exec("mplayer -vo null -vc dummy -af resample=44100 -ao pcm:waveheader ".$filename);
//echo shell_exec("whoami");
//	exec("lame -m s -b ".intval($bitrate)."audiodump.wav -o ".$filename);
	exec("lame -b ".intval($bitrate)." audiodump.wav ".$filename);

	exec("rm audiodump.wav");
//exit();

	return $filename;
}
$user=new Me2AuthenticatedUser($_POST["me2id"], $_POST["me2key"]);

class att{
    const MaxSize = 10485760;
	var $name;
    var $mime = 'audio/mpeg';
    function __toString() {
        return (string) $this->binary;
    }
    function __construct($filename) {
        
        $fp=fopen($filename,"r");
        $binary = fread($fp,filesize($filename));
        if (strlen($binary) > self::MaxSize) {
    	     throw new UnexpectedValueException(self::MaxSize . "Bytes 이하의 파일만 지원합니다.");
   		 }
        fclose($fp);
        
        $this->binary = $binary;
        $this->name = $filename;
    }

}

$body=$_POST["body"];
$tags="shutuploader ".$_POST["tags"];
$icon=1;
$comments = Me2Comment::Opened;
$filename=movefile($_FILES["userfile"]["tmp_name"]);
//echo "파일을 받았습니다.";
//echo "파일 변환을 시작합니다.";
$filename=convert_file($filename,$_POST['bitrate']);
echo $filename."<br />";
echo filesize($filename)."<br />";
flush();
$attachment=new att($filename);
//echo "미투데이로 전송합니다.";
//exit();

$params=array(
'post[body]' => (string) $body,
'post[tags]' => trim(join(' ', (array) $tags)),
'post[icon]' => $icon,
'receive_sms' => $bool[$comments === Me2Comment::ReceiveSMS],
'close_comment' => $bool[$comments === Me2Comment::Closed]
);
$param = 'attachment';
$params[$param] = $attachment;
$http_method = Me2Api::HttpPost;
//exit();
//print_r($params);
Me2Api::call("create_post/{$user->name}", $params, $user, $http_method);
?>