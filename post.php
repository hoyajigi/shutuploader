<?
//phpinfo();
//print_r($_FILES["userfile"]);
//echo "";
//exit();
set_time_limit (0);


/*
WMA 2 MP3 Encoding
ref:http://www.linuxquestions.org/linux/answers/Applications_GUI_Multimedia/Convert_WMA_to_MP3

#remove spaces
#remove uppercase
#Rip with Mplayer / encode with LAME
mplayer -vo null -vc dummy -af resample=44100 -ao pcm:waveheader $i
lame -m s audiodump.wav -o $i
#convert file names

rm audiodump.wav
*/

//echo "OK";
//connection_aborted();
//ignore_user_abort();
//header("Connection: close"); 
//session_write_close();
//require_once('pstool.inc.php');






include_once('upload.php');

?>