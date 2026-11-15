<?php
session_start();

$likeFile = __DIR__ . '/likes.json';
$articleId = '1';

if (empty($_SESSION['like_token'])) {
    $_SESSION['like_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['like_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['like_token'], $token)) {
        http_response_code(403);
        exit('Invalid request.');
    }

    $handle = fopen($likeFile, 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        http_response_code(500);
        exit('Could not save your like.');
    }

    $storedLikes = json_decode(stream_get_contents($handle), true);
    if (!is_array($storedLikes)) {
        $storedLikes = [];
    }
    $storedLikes[$articleId] = max(0, (int) ($storedLikes[$articleId] ?? 0)) + 1;
    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($storedLikes, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'), true, 303);
    exit;
}

$likeCount = 0;
if (is_file($likeFile)) {
    $handle = fopen($likeFile, 'r');
    if ($handle !== false) {
        if (flock($handle, LOCK_SH)) {
            $storedLikes = json_decode(stream_get_contents($handle), true);
            if (is_array($storedLikes)) {
                $likeCount = max(0, (int) ($storedLikes[$articleId] ?? 0));
            }
            flock($handle, LOCK_UN);
        }
        fclose($handle);
    }
}
?>
<html>
    <head>
        <title>適当ブログ</title>
        <meta charset="utf-8">

        <style>
            body {
                background-color: aquamarine;
                text-align: center;
                margin: 0;
                padding: 1rem;
                box-sizing: border-box;
                overflow-wrap: anywhere;
            }
        </style>

        <link rel="stylesheet" href="styles/style.css">
    </head>

    <body>
        <h1>適当ブログ</h1>
        <p>Blogs</p>
        <hr>

        <article class="articles">
            <h2><a href="articles/2.html">ブログ作ったよ</a></h2>
            <hr>
            <p>2026/11/15</p>
            <form method="post">
                <input type="hidden" name="like_token" value="<?= htmlspecialchars($_SESSION['like_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="like" type="submit">いいね　<span class="like-count"><?= $likeCount ?></span></button>
            </form>
        </article>

        <article class="articles">
            <h2><a href="articles/1.html">テスト</a></h2>
            <hr>
            <p>0000/00/00</p>
            <form method="post">
                <input type="hidden" name="like_token" value="<?= htmlspecialchars($_SESSION['like_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="like" type="submit">いいね　<span class="like-count"><?= $likeCount ?></span></button>
            </form>
        </article>

     <!--カウンター画像からは配布サイトへリンクが設定されています。誤作動、誤表示の原因となりますのでコードの改変、改行は行わないで下さい。
    このコメント部分は削除して頂いても結構です。-->
    <a href="https://www.stylemap.co.jp/"><img src="https://www.f-counter.net/j/68/1794741994/" alt="アクセスカウンター"></a>

</html>
