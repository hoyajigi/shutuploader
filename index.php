<!DOCTYPE html PUBLIC
    "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<meta name="imagetoolbar" content="no" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<title>Shutuploader</title>
<style>
body{ padding: 2.5em 0; width: 30em; margin: 0 auto; font-family:"Trebuchet MS","Lucida Grande","Tahoma","Helvetica","Arial","hiragino kaku gothic pro","NanumGothic","NanumGothicOTF",sans-serif;}
dl { overflow: hidden; }
dl dt, dl dd { float: left; }
dl dt { clear: left; width: 7.7em; padding: 1.2em 0; }
dl dt label { font: bold 1.2em Tahoma, sans-serif;}
dl dd { padding: .5em 0; }
dl dd input { border: 1px solid #ff256c; font: normal 1.7em Tahoma, sans-serif;  }
dl dd select { border: 1px solid #ff256c; font: normal 1.7em Tahoma, sans-serif; width: 11em;}
dl dd textarea { border: 1px solid #ff256c; font: normal 1.6em "Trebuchet MS","Lucida Grande","Tahoma","Helvetica","Arial","hiragino kaku gothic pro","NanumGothic","NanumGothicOTF",sans-serif;
}
ul { text-align: center; }
ul li { display: inline; padding: 0 .5em; }
ul li input { font: bold 1.2em Tahoma, sans-serif; padding: .2em 1.2em; }
ul,
ol       { list-style: none; }
img,
fieldset { border: 0; }

</style>
</head>
<body>
<form action="post.php" method="post" enctype="multipart/form-data">
	<fieldset>
		<dl>
			<dt><label for="me2id">Me2Day ID</label></dt>
			<dd><input type="text" name="me2id" /></dd>
			<dt><label for="me2key">API Key</label></dt>
			<dd><input type="text" name="me2key" id="me2key" /></dd>
			
			<dt><label for="body">본문</label></dt>
			<dd><textarea name="body"></textarea></dd>
			<dt><label for="tags">태그</label></dt>
			<dd><input type="text" name="tags"/></dd>
			
			<dt><label for="bitrate">음질</label></dt>
			<dd><select name="bitrate">
			<option value="8">저음질</option>
			<option selected="selected" value="16">중음질</option>
			<option value="32">고음질</option>
			</select></dd>
			
			<dt><label for="userfile">파일</label></dt>
			<dd><input name="userfile" type="file"/></dd>
			
		</dl>
		<ul>
			<li><input type="submit" value="Submit" /></li>
		</ul>
	</fieldset>
</form>
</body>
</html>