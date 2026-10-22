<?php
declare(strict_types=1);
session_start();

const DATA_FILE = __DIR__ . '/data/posts.json';
const ADMIN_PASSWORD = ''; // Change this before publishing the board.

if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0775, true);
}
if (!file_exists(DATA_FILE)) {
    file_put_contents(DATA_FILE, "[]", LOCK_EX);
}

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function text_length(string $value): int {
    $count = preg_match_all('/./us', $value, $matches);
    return $count === false ? strlen($value) : $count;
}
function read_posts(): array {
    $raw = @file_get_contents(DATA_FILE);
    $posts = json_decode($raw ?: '[]', true);
    return is_array($posts) ? $posts : [];
}
function write_posts(array $posts): bool {
    $json = json_encode(array_values($posts), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
    return $json !== false && file_put_contents(DATA_FILE, $json, LOCK_EX) !== false;
}
function redirect_home(): never { header('Location: index.php'); exit; }

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        $error = '画面を更新してから、もう一度お試しください。';
    } else {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'post') {
            $nickname = trim((string)($_POST['nickname'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));
            if ($nickname === '') $nickname = '名無し';
            if (text_length($nickname) > 24) $error = 'ニックネームは24文字以内で入力してください。';
            elseif ($message === '') $error = '本文を入力してください。';
            elseif (text_length($message) > 1000) $error = '本文は1000文字以内で入力してください。';
            else {
                $posts = read_posts();
                array_unshift($posts, ['id' => bin2hex(random_bytes(8)), 'nickname' => $nickname, 'message' => $message, 'created_at' => date('Y-m-d H:i')]);
                if (!write_posts(array_slice($posts, 0, 300))) $error = '投稿を保存できませんでした。';
                else {
                    setcookie('keijiban_nickname', $nickname, ['expires' => time() + 60 * 60 * 24 * 365, 'path' => '/', 'samesite' => 'Lax']);
                    redirect_home();
                }
            }
        } elseif ($action === 'admin_login') {
            if (hash_equals(ADMIN_PASSWORD, (string)($_POST['password'] ?? ''))) {
                session_regenerate_id(true); $_SESSION['admin'] = true; $_SESSION['csrf'] = bin2hex(random_bytes(32)); redirect_home();
            }
            $error = 'パスワードが違います。';
        } elseif ($action === 'admin_logout') {
            unset($_SESSION['admin']); redirect_home();
        } elseif ($action === 'delete' && !empty($_SESSION['admin'])) {
            $id = (string)($_POST['id'] ?? '');
            $posts = array_values(array_filter(read_posts(), fn($post) => (string)($post['id'] ?? '') !== $id));
            if (write_posts($posts)) redirect_home();
            $error = '削除できませんでした。';
        }
    }
}

$posts = read_posts();
$nicknameValue = (string)($_COOKIE['keijiban_nickname'] ?? '');
$adminOpen = isset($_GET['admin']) || !empty($_SESSION['admin']);
?><!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>vista掲示板</title>
  <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<div class="page">
  <header class="site-header">
    <div class="header-inner"><div><h1>自作掲示板</h1></div></div>
    <nav class="top-nav"><a href="index.php">掲示板トップ</a><a href="#write">新しく投稿する</a><a href="?admin=1">管理者メニュー</a></nav>
  </header>

  <main>
    <section class="welcome panel"><p>Codexに全部丸投げで作らせた掲示板です。</p><div class="welcome-note">★ 個人情報や、だれかを傷つける内容は書き込まないでね ★</div></section>

    <?php if ($error !== ''): ?><div class="alert" role="alert"><?= h($error) ?></div><?php endif; ?>

    <?php if ($adminOpen): ?>
      <section class="panel admin-panel"><div class="section-title"><span class="title-icon">◆</span><h2>管理者メニュー</h2></div>
        <?php if (!empty($_SESSION['admin'])): ?>
          <p class="admin-status">管理者としてログイン中です。投稿一覧から書き込みを削除できます。</p>
          <form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="admin_logout"><button class="button button-sub" type="submit">ログアウト</button></form>
        <?php else: ?>
          <p>管理者パスワードを入力してください。</p><form method="post" class="admin-login"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="admin_login"><label for="password">パスワード</label><input id="password" type="password" name="password" required autocomplete="current-password"><button class="button" type="submit">ログイン</button></form>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <section class="panel form-panel" id="write"><div class="section-title"><span class="title-icon">✎</span><h2>新規投稿</h2></div>
      <form method="post" class="post-form"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="post">
        <label for="nickname">ニックネーム <span class="optional">（省略可）</span></label><input id="nickname" name="nickname" maxlength="24" value="<?= h($nicknameValue) ?>" placeholder="ななしさん" autocomplete="nickname">
        <label for="message">メッセージ</label><textarea id="message" name="message" rows="4" maxlength="1000" required placeholder="メッセージを書いてね！"></textarea>
        <div class="form-bottom"><span class="help-text">ニックネームは次回から自動で入力されます。</span><button class="button" type="submit">投稿する</button></div>
      </form>
    </section>

    <section class="panel posts-panel"><div class="section-title"><span class="title-icon">▤</span><h2>みんなの投稿</h2><span class="count"><?= count($posts) ?>件</span></div>
      <?php if (!$posts): ?><div class="empty-state">まだ投稿はありません。</div><?php else: ?>
        <div class="post-list">
        <?php foreach ($posts as $i => $post): ?>
          <article class="post-card"><div class="post-heading"><span class="post-number">No.<?= count($posts) - $i ?></span><strong class="post-name"><?= h((string)($post['nickname'] ?? 'ななしさん')) ?></strong><time><?= h((string)($post['created_at'] ?? '')) ?></time></div><div class="post-message"><?= nl2br(h((string)($post['message'] ?? ''))) ?></div>
          <?php if (!empty($_SESSION['admin'])): ?><form method="post" class="delete-form" onsubmit="return confirm('この投稿を削除しますか？');"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h((string)($post['id'] ?? '')) ?>"><button type="submit" class="delete-button">この投稿を削除</button></form><?php endif; ?></article>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
  <footer><p>自作掲示板 <span>© 2026 vista_lab</span></p><a href="?admin=1">管理者</a></footer>
</div>
<script src="script.js"></script>
</body></html>
