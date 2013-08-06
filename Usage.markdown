Me2PHP
======

Me2PHP는 [미투데이][me2day]의 [OpenAPI][me2api]를 [PHP][]에서 사용할 수 있게
해주는 라이브러리입니다. 지연 평가(lazy evaluation)를 반복자로 구현하기 위해
[SPL][]을 사용하였습니다.

**현재 [`get_posts`](http://codian.springnote.com/pages/386176) 관련 문제가 있습니다. [해당 이슈](http://groups.google.com/group/me2day_developers_network/msg/c1eb5a2c9797d1d1?pli=1)가 해결되면 Me2PHP에서 적절히 수정할 예정입니다.**

 *[SPL]: Standard PHP Library
  [me2day]: http://me2day.net/
  [me2api]: http://codian.springnote.com/pages/86001  "Me2API"
  [php]: http://www.php.net/
  [spl]: http://www.php.net/~helly/php/ext/spl/


작성자: 홍민희
--------------

Me2PHP에 관해 버그 신고를 하거나, 기타 문의할 부분이 있다면
작성자([홍민희][author])에게 연락을 주세요. [제 미투데이][1]나 이메일 등으로
연락하시면 됩니다. 이메일 주소는 [제 홈페이지][author]에 가면 찾을 수 있습니다.

  [author]: http://dahlia.kr/ "dahlia.kr"
  [1]: http://me2day.net/dahlia "dahlia's me2day"


다운로드
--------

Me2PHP는 제 개인 소스 코드 저장소에서 받을 수 있습니다.

    hg clone http://hg.ruree.net/~dahlia/me2php/


의존성
------

Me2PHP는 다음과 같은 조건이 만족해야 사용할 수 있습니다.

- [PHP][] 5.1 이상 (`DateTime` 클래스)
- [SPL][] (`ArrayAcces`, `IteratorAggregate`, `Countable`, `ArrayIterator`)
- [SimpleXML][2]

의존성을 만족하는지 다음 방법으로 확인할 수 있습니다.

    $ php -r "echo version_compare(PHP_VERSION, '5.1', '>=') && \
              extension_loaded('spl') && extension_loaded('simplexml') ? \
              'okay' : 'sorry';"
    okay

  [2]: http://www.php.net/manual/en/ref.simplexml.php


라이브러리 로드
---------------

Me2PHP를 이용하기 위해서는 `Me2.php`를 포함시켜야 합니다.

    require_once 'Me2.php';

혹은 필요한 클래스만 포함하셔도 됩니다. 내부적으로 서로 의존성이 있는 클래스들은
함께 포함됩니다.

    require_once 'Me2/AuthenticatedUser.php';
    require_once 'Me2/Post.php';

라이브러리를 로드한 다음에는 Me2API를 사용하기 위해서 어플리케이션 키를 설정해야
합니다. 어플리케이션 키가 없으시다면 [발급][3]받으셔야 합니다. 자세한 내용은
[Me2API 설명서][me2api]를 참고하세요.

    Me2Api::$applicationKey = 'your me2api application key goes here';

어플리케이션 키를 설정하지 않거나, 유효하지 않은 어플리케이션 키를 설정하고
라이브러리를 사용할 경우 `Me2ApplicationKeyException` 예외가 발생하게 됩니다.

  [3]: http://me2day.net/api/front/appkey  "Me2API 어플리케이션 키 요청"


클래스들
--------

Me2PHP는 다섯 개의 모델 클래스들과(하나는 추상 클래스), 여덟 개의 리스트
클래스들(하나는 추상 클래스), 하나의 원격 호출 추상 클래스, 예외 클래스 둘로
이루어져 있습니다.


### 모델 클래스 ###

- `Me2Person` — 미투데이 사용자를 추상화합니다.
  - `Me2AuthenticatedUser` — me2API 사용자 키로 인증이 된 사용자를 추상화합니다.
- `Me2Post` — 미투데이에 올라온 글을 추상화합니다.
- `Me2Comment` — 글에 달린 댓글을 추상화합니다.


### 리스트 클래스 ###

리스트 클래스는 여러 개의 모델 인스턴스들을 담는 컨테이너이기도 하며, 지연
평가를 하기 위해 원격 호출을 하기 전에 질의문을 구성하는 용도로도 사용됩니다.
다음의 리스트 클래스들에 대해서는 사실 *의식하지 않고 그냥 배열이라고 생각하고*
사용하셔도 배열과 인터페이스가 흡사하기 때문에 상관이 없습니다.

- `Me2PersonList` — `Me2Person` 인스턴스(사용자)들을 담습니다.
  - `Me2FriendList` — 특정 `Me2Person`(사용자)의 친구들을 요청하고 담습니다.
- `Me2TaggedPersonList` — 마이태그를 설정한 특정 `Me2Person`(사용자)의 친구들을
                          요청하고 담습니다.
- `Me2BasePostList`
  - `Me2PostList` — 특정 `Me2Person`(사용자)이 작성한 `Me2Post` 인스턴스(글)들을
                    담습니다.
  - `Me2RecentPostList` — `Me2PostList`와 비슷하지만, 최근글들을 담습니다.
- `Me2TagList` — 특정 `Me2Post`(글)에 달린 태그들을 담습니다.
- `Me2CommentList` — 특정 `Me2Post`(글)에 달린 `Me2Comment`(댓글)들을 요청하고
                     담습니다.


### `Me2Api` 클래스 ###

`Me2Api` 클래스는 어플리케이션 키를 관리하고 내부적으로 Me2API 원격 호출을 하고
결과로 받은 XML을 해석하여 돌려주는 추상 클래스(`abstract class`)로, 사용할 때는
어플리케이션 키를 설정하는 것 말고는 신경쓸 필요가 없습니다.


### 예외 클래스 ###

- `Me2Exception` — 내부적으로 호출시 네트워크 문제 등, 잡다한 대부분의 예외들을   추상화합니다.
  - `Me2UnauthenticatedException` — `Me2AuthenticatdUser` 클래스를 사용할 때
                                    발생합니다. 인증이 필요한 부분(마이태그 친구
                                    목록, 글 작성, 답변 작성)에서 인증된
                                    사용자(`Me2AuthenticatedUser`)가 필요한데
                                    일반 사용자 인스턴스(`Me2Person`)를
                                    전달했거나, 사용자 키가 유효하지 않은 경우에                                    발생합니다.
  - `Me2MetooByOneselfException` - 스스로 쓴 글에 미투했을 때 발생합니다.


친구 목록                                                  {#how-to-get-friends}
--------

특정 사용자의 친구들을 얻기 위해서는, 일단 “특정 사용자”를 얻어야 합니다.

    $user = new Me2Person('dahlia');

사용자 이름을 인자로 전달하여 `Me2Person`이나 `Me2AuthenticatedUser`(이 클래스는
`Me2Person`을 상속하므로 `Me2Person`을 사용하는 모든 곳에 호환됩니다) 인스턴스를
생성합니다. _사용자 이름은 미투데이 주소 **http://me2day.net/name**에서 끝쪽의 **name** 부분을 말합니다._

사용자 객체의 `friends` 멤버를 통해 친구들을 얻을 수 있습니다.

    <h3>내 미친들</h3>
    <ul>
        <?php foreach($user->friends as $friend): ?>
            <li><a href="<?php echo $friend->url ?>"><?php
                echo $friend ?></a></li>
        <?php endforeach ?>
    </ul>

친한 친구(`$user->friends->close`)나 직계존속(`$user->friends->families`),
지지자(`$user->friends->supporters`)—[사용자 인증](#how-to-authenticate-user)
필요—도 저런 식으로 구하면 됩니다.

마이태그를 설정한 친구들만 구할 수도 있는데, 이것은 `Me2Person` 객체로는 안되고,
`Me2AuthenticatedUser` 객체를 이용해야 합니다.

    $user = new Me2AuthenticatedUser('dahlia', "dahlia's user key goes here");
    foreach($user->friends->tags['선린인터넷고등학교'] as $friend)
        echo $friend;

이어서 쓰면 친구의 친구도 구할 수 있겠죠. 아래 코드는 `$user`의
“선린인터넷고등학교” 마이태그를 가진 친구들 중 첫번째 사용자의 친한 친구들 수가
얼마나 되는지 알아내는 코드입니다.

    count($user->friends->tags['선린인터넷고등학교'][0]->friends->close)

참고로 `Me2Person`은 `__toString()` 메서드를 구현하고 있으므로, 그냥 출력하거나
`(string)`으로 캐스팅할 경우 사용자 이름이 나옵니다.


글 목록                                                      {#how-to-get-posts}
-------

글 목록은 사용자 객체의 `posts` 멤버를 통해 얻을 수 있습니다.

    <?php foreach($user->posts as $post): ?>
        <div class="post">
            <img src="<?php echo $post->icon ?>" class="icon" alt="<?php
                echo $post->kind ?>" />
            <p><a href="<?php echo $post->url ?>"><?php
				echo strip_tags($post) ?></a></p>
            <p>사람들이 이 글에 <?php echo $post->metoo ?>번 미투했어요~.</p>
        </div>
    <?php endforeach ?>

(`Me2Person`과 마찬가지로 `__toString()` 메서드가 글 내용을 반환하도록 구현되어
 있습니다. 또 글 내용은 HTML로 되어 있으므로, 그냥 출력하면 링크가 함께 나오게
 됩니다. 위 예제에서는 내용 안에 든 링크를 제거하기 위해 `strip_tags()` 함수와
 함께 사용했습니다.)

위와 같이 할 경우 전체 글이 반환되겠죠. 특정 태그를 포함한 글만 선택할 수도
있습니다.

    $user->posts['me2sms']

여러 태그가 동시에 포함된 글들만 뽑을 수도 있습니다. 공백으로 여러 태그를 써도
되고, 다차원 배열을 쓰듯이 연달아 인덱스 연산자를 붙여도 됩니다. 태그의 배열을
넣어도 됩니다.

	$posts = $user->posts['vlaah 야간개발팀 공지'];
	$posts = $user->posts['vlaah']['야간개발팀']['공지'];
	$posts = $user->posts[array('vlaah', '야간개발팀', '공지')];

특정 날짜 이후의 글만 선택할 수도 있습니다. 아래와 같이 할 경우 2007년 8월 1일
이후부터 지금까지의 글들이 모두 선택됩니다.

    $user->posts->from(new DateTime('2007-08-01'))

특정 날짜 이전의 글을 선택하는 것도 가능합니다. 아래와 같이 할 경우 첫 글부터
2007년 9월 10일 이전까지의 글들이 모두 선택됩니다.

    $user->posts->to(new DateTime('2007-09-10'))

둘을 함께 쓸 수도 있습니다.

    $user->posts->from(new DateTime('2007-08-01'))
                ->to  (new DateTime('2007-09-10'));
    $user->posts->between(
        new DateTime('2007-08-01'),
        new DateTime('2007-09-10')
    );

위 두 코드는 동일한 작동을 합니다. `between()` 메서드를 쓰는 쪽이 약간 더
짧습니다.

위 세 조건을 모두 섞어서 쓸 수 있는데, PHP 해석기가 허접이라 인덱스 연산자
뒤에서 메서드 체이닝하는 것은 되는데, 메서드나 함수 호출 표현식 뒤에 인덱스
연산자가 오는 것은 오류를 내뱉습니다. 따라서, 위 조건들을 섞을 때는 맨 처음
태그부터 선택해줍니다.

    $user->posts['tag']->between('2007-08-01', mktime(0, 0, 0, 9, 10, 2007))

아, 위와 같이 굳이 `DateTime` 인스턴스를 전달하지 않고 문자열이나 기존에 PHP에서
사용하던 [UNIX 타임스탬프][4]를 전달해도 됩니다.

사용자 객체의 `recentPosts` 멤버를 이용하면 간편하게 최근 글을 뽑을 수도
있습니다.

    $user->recentPosts

  [4]: http://en.wikipedia.org/wiki/Unix_time


글에 달린 태그
--------------

`Me2Post` 인스턴스의 `tags` 멤버를 통해 해당 글에 달린 태그들을 얻을 수
있습니다. 이것은 배열과 비슷한 인터페이스를 구현한 `Me2TagList` 인스턴스로,
인덱스 연산자에 숫자를 넣거나 `foreach`문 등으로 반복할 때는 태그 이름만 나오고,
인덱스 연산자에 문자열로 된 태그를 키로 넣을 경우 해당 태그에 대한 URL이 나오게
되어 있습니다.

    <ul>
        <?php foreach($post->tags as $tag): ?>
            <li><a href="<?php echo $post->tag[$tag] ?>"><?php
                echo htmlspecialchars($tag) ?></a></li>
        <?php endforeach ?>
    </ul>

글에 태그가 달렸는지 확인할 때는

- `Me2Post->tags->contains(string, string…)`
- `Me2Post->tags->containsAll(array(string, string…))`
- `Me2Post->tags->containsAny(array(string, string…))`

메서드를 사용하면 됩니다.

	assert($post instanceof Me2Post);
	assert(array('a', 'b', 'c') == $post->tags->toArray());

	assert($post->tags->contains('a'));
	assert($post->tags->contains('a', 'b', 'c'));
	assert(!$post->tags->contains('d'));
	assert(!$post->tags->contains('a', 'd'));

	assert($post->tags->containsAll(array('a')));
	assert($post->tags->containsAll(array('a', 'b', 'c')));
	assert(!$post->tags->containsAll(array('d')));
	assert(!$post->tags->containsAll(array('a', 'd')));

	assert($post->tags->containsAny(array('a')));
	assert($post->tags->containsAny(array('a', 'b', 'c')));
	assert($post->tags->containsAny(array('a', 'd')));
	assert(!$post->tags->containsAny(array('d')));
	assert(!$post->tags->containsAny(array('d', 'e')));


글쓴 시각
---------

`Me2Post` 인스턴스의 `createdAt` 멤버를 통해 글쓴 시각을 구할 수 있습니다.
`DateTime` 객체이므로 `format()` 메서드로 서식화할 수 있습니다.

    echo $post->createdAt->format('Y-m-d H:i:s');


글쓰기                                                      {#how-to-write-post}
------

마이태그 때와 마찬가지로 글쓰기 역시 인증이 필요하기 때문에
`Me2AuthenticatedUser` 인스턴스를 이용합니다. 아래 코드는 `$user`가
`Me2AuthenticatedUser` 인스턴스라고 단언한 예제입니다. `Me2AuthenticatedUser`
인스턴스를 얻는 방법은 [사용자 인증하기](#how-to-authenticate-user) 부분을
참고하세요.

    assert($user instanceof Me2AuthenticatedUser);

    $post = $user->post(
        '150자 글 내용. "링크":http://me2day.net/dahlia  역시 가능. 필수 인자.',
        '태그는 공백으로 구분된 문자열도 좋고 배열도 됨 선택 인자',
        1 # 아이콘은 1부터 12까지 가능.
    );

`Me2AuthenticatedUser->post()` 메서드를 사용하는 대신 `Me2Post` 객체를 직접
생성해도 똑같은 효과를 지닙니다.

    assert($user instanceof Me2AuthenticatedUser);
    $post = new Me2Post($user, '글 내용.', array('태그'), 12);

이렇게 했을 때 `$post` 객체는 `$user->posts[0]`를 이용해 받은 `Me2Post`
인스턴스와 똑같은 종류로, 동일하게 사용할 수 있습니다.

    echo join(' ', $post->tags);


댓글 구하기                                               {#how-to-get-comments}
-----------

글 객체(`Me2Post` 인스턴스)의 `comments` 멤버를 이용해 댓글 목록을 받을 수
있습니다.

    <ul>
        <?php foreach($post->comments as $comment): ?>
            <li><a href="<?php echo $comment->author->url ?>"><?php
                    echo htmlspecialchars($comment->author->nick) ?></a>
                <p><?php echo $comment ?></p>
                <span class="created-at"><?php
                    echo $comment->createdAt->format('Y-m-d H:i:s')
                ?></span></li>
        <?php endforeach ?>
    </ul>

코멘트 객체(`Me2Comment` 인스턴스, 위 코드에서는 `$comment`)는 `Me2Person`
인스턴스로 된 작성자 정보(`author` 멤버), `DateTime` 인스턴스로 된 작성일
정보(`createdAt` 멤버)도 함께 지닙니다.


댓글 쓰기                                                {#how-to-write-comment}
---------

특정 글에 댓글을 달 수 있습니다. 이것 역시 [글쓰기](#how-to-write-post)와
마찬가지로 `Me2AuthenticatedUser` 인스턴스를 사용해야합니다. (`Me2Person`으로도
글을 쓸 수 있다면, 남의 이름을 도용해서 Me2APP 제작자가 맘대로 아무 곳에나
댓글을 달 수 있을테니까요.)

    assert($user instanceof Me2AuthenticatedUser);
    assert($post instanceof Me2Post);

    $comment = $user->comment($post, '댓글 내용.');

`Me2AuthenticatedUser->comment()` 메서드 외에도 `Me2Post->comment()` 메서드를 쓸
수도 있고, `Me2Comment` 인스턴스를 직접 생성하는 방법도 있습니다. 동작은 모두
동일합니다.

    $comment = $post->comment($user, '댓글 내용.');
    $comment = new Me2Comment($user, $post, '댓글 내용.');


댓글 지우기                                             {#how-to-delete-comment}
-----------

`Me2Comment->delete()` 메서드를 이용하여 댓글을 삭제할 수도 있습니다.
[글쓰기](#how-to-write-post)나 [댓글 쓰기](#how-to-write-comment)처럼 댓글 삭제
역시 [사용자 인증](#how-to-authenticate-user)이 필요합니다. 다만, *코멘트
작성자의 인증이 아니라, 코멘트가 달린 포스트의 작성자의 인증*이 필요합니다.

    assert($user instanceof Me2AuthenticatedUser);
    assert($comment instanceof Me2Comment);
    assert($user->isAuthorOf($comment->post));

    $comment->delete($user);

권한이 없을 경우 `Me2Exception` 예외를 던집니다.


사용자 인증하기                                      {#how-to-authenticate-user}
---------------

[글 목록](#how-to-get-posts)이나 [친구들의 목록](#how-to-get-friends),
[답변](#how-to-get-comments) 등을 가져오는 것은 `Me2Person` 인스턴스로도 할 수
있지만, [글쓰기](#how-to-write-post)나 [답변 달기](#how-to-write-comment)처럼
스스로를 증명해야하는 기능들은 `Me2AuthenticatedUser` 인스턴스가 필요합니다.
`MeAuthenticatedUser`는 `Me2Person`의 일종(subclass)입니다.

`Me2AuthenticatedUser` 인스턴스 역시 `Me2Person`처럼 `new` 키워드를 통해
생성자에 사용자 이름을 인수로 전달하여 생성합니다. 단, _두번째 인자로 사용자
키를 받습니다._

    $user = new Me2AuthenticatedUser('dahlia', "dahlia's user key goes here");

이미 `Me2Person` 인스턴스를 가지고 있다면, 그것으로부터 `Me2AuthenticatedUser`
인스턴스를 구하는 방법도 있습니다.

    assert($person instanceof Me2Person);
    $user = $person->authenticate("dahlia's user key goes here");

한줄 소개 읽고 쓰기
-------------------

미투데이의 한줄 소개는 `Me2Person->description` 멤버에 저장되어 있습니다.

	<?php assert($person instanceof Me2Person) ?>

	<dl class="me2person" id="me2person-<?php echo $person->id ?>">
		<dt>닉네임</dt>
		<dd><?php echo htmlspecialchars($person) ?></dd>
		<dt>한줄 소개</dt>
		<dd><?php echo htmlspecialchars($person->description) ?></dd>
	</dl>

한줄 소개를 수정하려면 [사용자 인증](#how-to-authenticate-user)을 해야합니다.

	assert($person instanceof Me2AuthenticatedUser);
	$person->description = '설정할 한줄 소개';

디버그 쉽게 하기
----------------

`Me2Api::$debugLogger` 정적 멤버에 함수를 지정하여 쉽게 HTTP 로그를 확인할 수
있습니다.

    Me2Api::$debugLogger = 'var_dump';

[Phunctional][5]과 함께 사용할 경우, [Phunctional][5] 방식의
함수자(functor)—`Callable` 인스턴스 등—로도 지정할 수 있습니다. 아래 예제는
로그를 `me2.php.log` 파일에 저장하는 코드입니다.

    require_once 'Phunctional.php';

    $logFile = fopen('me2php.log', 'a');

    Me2Api::$debugLogger = def(&$logMessage)?
        fwrite($logFile, $logMessage)
    :fed();

    fclose($logFile);

아래 예제는 로그를 HTML 주석으로 출력하는 코드입니다.

    require_once 'Phunctional.php';

    Me2Api::$debugLogger = def(&$logMessage)?
        print "<!-- $logMessage -->"
    :fed();

 *[HTTP]: Hypertext Transfer Protocol
  [5]: http://phunctional.dahlia.kr/

간단 레퍼런스                                                       {#reference}
-------------

### `Me2API` ###

#### 정적 멤버 ####

`$applicationKey`
:    (`string`) Me2API 어플리케이션 키. Me2PHP 사용하기 전에 초기화가 필요하다.

`$debugLogger`
:    (`callback|functor`) 디버그를 위해 HTTP 요청 및 응답 문자열을 인수로 받아
     출력하는 함수. [Phunctional][5] 스타일도 함수 객체도 지원한다.

### `Me2Person` ###

#### 생성자: `new Me2Person(string $name, boolean $checkExistance = false)` ####

사용자 이름이 `$name`인 객체를 생성한다. `$name`이 미투데이의 이름 규칙과 맞지
않을 경우 `UnexpectedValueException` 예외를 낸다. `$checkExistance`가 `true`일
경우, 해당 `$name`의 사용자가 있는지 확인하여 `Me2Exception` 예외를 낸다.

#### 멤버 ####

`id`
`name`
:   (`string`) 사용자 이름.

`url`
`me2dayHome`
:   (`string`) 미투데이 URL.

`openId`
`openID`
`openid`
:   (`string`) OpenID 식별자 URL.

`nick`
`nickName`
`nickname`
`screenName`
`screenname`
:   (`string`) 별명.

`homepage`
:   (`string`) 사용자의 블로그, 홈페이지 URL.

`face`
`profileImage`
:   (`string`) 프로필 이미지 URL.

`signature`
`description`
:   (`string`) 사용자가 작성한 한줄 소개.
    (읽기만 가능. 쓰기는 인증된 `Me2AuthenticatedUser` 인스턴스에서만 가능.)

`rss`
`feed`
`rssDaily`
:   (`string`) 일간 RSS 피드 URL.

`inviter`
:   (`Me2Person`) 초대한 사용자.

`invitedBy`
:   (`string`) 초대한 사용자 이름. (`$person->inviter->name`)

`posts`
:   (`Me2PostList`, `array`-like) 글 목록.

`recentPosts`
`latestPosts`
:   (`Me2RecentPost`, `array`-like) 최근 글 목록.

`friends`
:   (`Me2FriendList`, `array`-like) 친구 목록.

`friendsCount`
:   (`int`) 친구 수. `count($person->friends)`를 사용하는 것과 효율에 차이가
    없으므로, 굳이 이것을 사용할 필요가 없다. (둘 다 원격 호출이 일어나지 않음.)

`updatedAt`
:   (`DateTime`) 최근 글 작성일.

`pubDate`
:   (`string`) [ISO 8601][6] 형식으로 서식화된 최근 글 작성일 문자열.

  [6]: http://www.w3.org/TR/NOTE-datetime  "Date and Time Formats"

#### 메서드 ####

`authenticate(string $key)`
:   (`Me2AuthenticatedUser`) `new Me2AuthenticatedUser($person->name, $key)`를
    반환한다.

`isAuthorOf(Me2Post|Me2Comment $entity)`
:   (`boolean`) `$entity` 객체의 작성자인지 확인한다.

`__toString()` 
:   (`string`) 사용자 이름을 반환한다.

### `Me2AuthenticatedUser` ###

`Me2Person`을 상속하므로, `Me2Person`의 모든 멤버와 메서드를 지닌다.

#### 생성자: `new Me2AuthenticatedUser($name, string $key, $validate = true)`

사용자 이름이 `$name`이고 Me2API 사용자 키가 `$key`인 인증된 사용자 객체를
생성한다. `$validate`가 `true`일 경우 생성시 `$key`의 유효성을 검사하여,
유효하지 않을 경우 예외를 던진다.

#### 멤버 ####

`apiKey`
:   (`string`) 생성할 때 입력했던 Me2API 사용자 키.

`myTags`
`mytags`
`tags`
:   (`array`) 마이태그들.

`myTagsInTab`
`mytagsInTab`
`tagsInTab`
:   (`array`) 자주 쓰는 마이태그들. (탭으로 보이는 마이태그들.)

#### 메서드 ####

`authenticate(string $key)`
:   (`Me2AuthenticatedUser`) `apiKey` 멤버를 `$key`로 바꾸고, 자기
    자신(`$this`)을 반환.

`validate(bool $throwException = false)`
:   (`bool`) 사용자 키가 올바른지의 여부를 반환한다. 인수로 `true`를 전달했을 때
    사용자 키가 올바르지 않다면 예외를 던진다.

`post(string $body, string|array $tags = '', int $icon = 1, int $comments = Me2Comment::Opened)`
:   (`Me2Post`) 글을 작성한다.
    `$comments`에는 다음과 같은 상수를 전달할 수 있다.

    `Me2Comment::Opened`
    `Me2Comment::Open`
    `Me2Comment::Allowed`
    `Me2Comment::Allow`
    :   댓글을 연다. 기본값.

    `Me2Comment::ReceiveSMS`
    `Me2Comment::ReceiveSms`
    :   댓글이 달리면 SMS로 받는다.

    `Me2Comment::Closed`
    `Me2Comment::Close`
    `Me2Comment::Deny`
    `Me2Comment::Denied`
    :   댓글을 닫는다.

`comment(Me2Post $post, string $body)`
:   (`Me2Comment`) 댓글을 작성한다.

`metoo(Me2Post $post)`
:   `$post` 글에 미투한다.


### `Me2Post` ###

#### 정적 메서드 ####

`fromUrl(string $url)`
:   (`Me2Post|null`) 퍼머링크에 해당하는 `Me2Post` 인스턴스를 반환한다. 잘못된
    퍼머링크 URL일 경우 `InvalidArgumentException`을 던진다. 찾는 글이 존재하지
	않을 경우 `null`을 반환한다.

`preview(string $text)`
:   (`string`) `$text` 문자열을 미투데이 포스팅 문법에 따라 HTML 문서로 변환하여
    반환한다. `<p>` 태그를 포함한다.

#### 생성자: `new Me2Post(Me2AuthenticatedUser $author, $body, string|array $tags = '', int $icon = 1, int $comments = Me2Comment::Opened)`

`$author` 사용자의 미투데이에 `$body` 내용으로 글을 작성한다.

`$comments`에는 다음과 같은 상수를 전달할 수 있다.

`Me2Comment::Opened`
`Me2Comment::Open`
`Me2Comment::Allowed`
`Me2Comment::Allow`
:   댓글을 연다. 기본값.

`Me2Comment::ReceiveSMS`
`Me2Comment::ReceiveSms`
:   댓글이 달리면 SMS로 받는다.

`Me2Comment::Closed`
`Me2Comment::Close`
`Me2Comment::Deny`
`Me2Comment::Denied`
:   댓글을 닫는다.

#### 멤버 ####

`url`
`permalink`
:   (`string`) 글의 URL.

`author`
:   (`Me2Person`) 작성자.

`content`
`body`
:   (`string`) 글 내용.

`tags`
:   (`Me2TagList`, `array`-like) 태그 목록.

`commentable`
:   (`boolean`) 댓글 가능 여부. (`$post->commentClosed` 멤버의 반대.)

`commentClosed`
:   (`boolean`) 댓글 닫기가 선택된 글이면 `true`.
                (`$post->commentable` 멤버의 반대.)

`comments`
:   (`Me2CommentList`, `array`-like) 달린 댓글 목록.

`commentsCount`
:   (`int`) 댓글 수. `count($post->comments)`를 사용하는 것과 효율에 차이가
    없으므로, 굳이 이걸 사용할 필요는 없다. (둘 다 원격 호출은 일어나지 않음.)

`type`
`kind`
:   (`string`) 글 종류. `think`, `feel`, `announce` 중 하나.

`iconUrl`
`icon`
:   (`string`) 글에 사용된 아이콘 이미지의 URL.

`metoo`
`metooCount`
:   (`int`) 미투 수.

`createdAt`
`publishedAt`
`postedAt`
:   (`DateTime`) 작성일.

`pubDate`
:   (`string`) [ISO 8601][6] 형식으로 서식화된 작성일 문자열.

#### 메서드 ####

`comment(Me2AuthenticatedUser $author, string $body)`
:   (`Me2Comment`) 글에 댓글을 작성한다.

`metoo(Me2AuthenticatedUser $author)`
:   글에 미투한다. 스스로 쓴 글에 미투할 경우 `Me2MetooByOneselfException`
    예외를 던진다. `metoo` 멤버도 1 증가한다.

`__toString()`
:   (`string`) 글 내용을 반환한다.


### `Me2Comment` ###

#### 생성자: `new Me2Comment(Me2AuthenticatedUser $author, Me2Post $post, string $body)`

`$author` 사용자의 명의로 `$post` 글에 `$body`라는 내용의 댓글을 작성한다.

#### 멤버 ####

`author`
:   (`Me2Person`) 댓글 작성자.

`post`
:   (`Me2Post`) 댓글이 달린 글.

`content`
`body`
:   (`string`) 댓글 내용.

`createdAt`
`publishedAt`
`postedAt`
:   (`DateTime`) 댓글 작성일.

#### 메서드 ####

`delete(Me2AuthenticatedUser $user)`
:   코멘트를 삭제한다.
    *`$user`는 코멘트 작성자가 아니라, 코멘트가 달린 포스트의 작성자여야 한다.*

`__toString()`
:   (`string`) 댓글 내용을 반환한다.

