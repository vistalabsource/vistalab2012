<?php
require __DIR__ . '/markdown.php';
session_start();

$id = $_GET['id'] ?? '';
$articles = blog_articles();
$article = null;
foreach ($articles as $candidate) {
    if ($candidate['id'] === $id) { $article = $candidate; break; }
}
if ($article === null) { http_response_code(404); exit('Article not found.'); }

if (empty($_SESSION['like_token'])) { $_SESSION['like_token'] = bin2hex(random_bytes(32)); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['like_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['like_token'], $token)) { http_response_code(403); exit('Invalid request.'); }
    if (empty($_SESSION['liked_articles'][$id])) {
        $path = __DIR__ . '/likes.json';
        $handle = fopen($path, 'c+');
        if ($handle === false || !flock($handle, LOCK_EX)) { http_response_code(500); exit('Could not save your like.'); }
        $counts = json_decode(stream_get_contents($handle), true);
        if (!is_array($counts)) { $counts = []; }
        $counts[$id] = max(0, (int) ($counts[$id] ?? 0)) + 1;
        rewind($handle); ftruncate($handle, 0); fwrite($handle, json_encode($counts, JSON_PRETTY_PRINT)); fflush($handle);
        flock($handle, LOCK_UN); fclose($handle);
        $_SESSION['liked_articles'][$id] = true;
    }
    header('Location: article.php?id=' . rawurlencode($id), true, 303); exit;
}

$counts = blog_like_counts();
$hasLiked = !empty($_SESSION['liked_articles'][$id]);
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></title>
        <style>
            body { background-color: aquamarine; text-align: center; margin: 0; padding: 1rem; box-sizing: border-box; overflow-wrap: anywhere; }
        </style>
        <link rel="stylesheet" href="styles/style.css">
    </head>
    <body>
        <article class="articles">
            <h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?></p>
            <hr>
            <div class="article-body"><?= blog_markdown($article['body']) ?></div>
            <hr>
            <form method="post">
                <!--カウンター画像からは配布サイトへリンクが設定されています。誤作動、誤表示の原因となりますのでコードの改変、改行は行わないで下さい。
                このコメント部分は削除して頂いても結構です。-->
                <a href="https://www.stylemap.co.jp/"><img src="https://www.f-counter.net/j/68/1794788819/" alt="アクセスカウンター"></a>

                <input type="hidden" name="like_token" value="<?= htmlspecialchars($_SESSION['like_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="like" type="submit" <?= $hasLiked ? 'disabled' : '' ?>><?= $hasLiked ? 'いいね済み' : 'いいね' ?>: <span class="like-count"><?= (int) ($counts[$id] ?? 0) ?></span></button>
            </form>
        </article>
        <p><a href="index.php">記事一覧へ戻る</a></p>
    </body>
</html>
