<?php
require __DIR__ . '/markdown.php';
session_start();

$articles = blog_articles();
$likeCounts = blog_like_counts();
if (empty($_SESSION['like_token'])) {
    $_SESSION['like_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>適当ブログ</title>
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
        <p>ここでは、私の日記でも綴っていきたいと思います。</p>
        <hr>
        <?php foreach ($articles as $article): ?>
        <?php $id = $article['id']; $hasLiked = !empty($_SESSION['liked_articles'][$id]); ?>
        <article class="articles">
            <h2><a href="article.php?id=<?= rawurlencode($id) ?>"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
            <hr>
            <p><?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars(blog_excerpt($article['body']), ENT_QUOTES, 'UTF-8') ?></p>
            <form method="post" action="article.php?id=<?= rawurlencode($id) ?>">
                <input type="hidden" name="like_token" value="<?= htmlspecialchars($_SESSION['like_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="like" type="submit" <?= $hasLiked ? 'disabled' : '' ?>><?= $hasLiked ? 'いいね済み' : 'いいね' ?>: <span class="like-count"><?= (int) ($likeCounts[$id] ?? 0) ?></span></button>
            </form>
        </article>
        <?php endforeach; ?>
        <!--カウンター画像からは配布サイトへリンクが設定されています。誤作動、誤表示の原因となりますのでコードの改変、改行は行わないで下さい。
        このコメント部分は削除して頂いても結構です。-->
        <a href="https://www.stylemap.co.jp/"><img src="https://www.f-counter.net/j/68/1794744402/" alt="アクセスカウンター"></a>

    </body>
</html>
