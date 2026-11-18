<?php
function blog_articles(): array
{
    $articles = [];
    foreach (glob(__DIR__ . '/articles/*.md') ?: [] as $path) {
        $id = basename($path, '.md');
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            continue;
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            continue;
        }
        // Accept both the simple header format and standard YAML front matter.
        if (preg_match('/^---\s*\R(.*?)\R---\s*\R(.*)$/s', $raw, $frontMatter)) {
            $metadata = [];
            foreach (preg_split('/\R/', $frontMatter[1]) ?: [] as $metadataLine) {
                if (preg_match('/^(title|date):\s*["\']?(.*?)["\']?\s*$/', trim($metadataLine), $field)) {
                    $metadata[$field[1]] = trim($field[2]);
                }
            }
            if (empty($metadata['title']) || empty($metadata['date'])) {
                continue;
            }
            $title = $metadata['title'];
            $date = $metadata['date'];
            $body = trim($frontMatter[2]);
        } elseif (preg_match('/^title:\s*(.+)\Rdate:\s*(.+)\R\R(.*)$/s', $raw, $matches)) {
            $title = trim($matches[1]);
            $date = trim($matches[2]);
            $body = trim($matches[3]);
        } else {
            continue;
        }
        $articles[] = [
            'id' => $id,
            'title' => $title,
            'date' => $date,
            'body' => $body,
            'mtime' => filemtime($path) ?: 0,
        ];
    }
    usort($articles, static fn($a, $b) => strcmp($b['date'], $a['date']) ?: ($b['mtime'] <=> $a['mtime']));
    return $articles;
}

function blog_like_counts(): array
{
    $data = is_file(__DIR__ . '/likes.json') ? json_decode((string) file_get_contents(__DIR__ . '/likes.json'), true) : [];
    return is_array($data) ? $data : [];
}

function blog_inline(string $text): string
{
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $text = preg_replace_callback('/`([^`]+)`/', static fn($m) => '<code>' . $m[1] . '</code>', $text);
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', static function ($m) {
        $url = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== null && !in_array(strtolower($scheme), ['http', 'https', 'mailto'], true)) {
            return $m[1];
        }
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $m[1] . '</a>';
    }, $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text);
    return $text;
}

function blog_markdown(string $markdown): string
{
    $lines = preg_split('/\R/', $markdown) ?: [];
    $html = [];
    $paragraph = [];
    $list = null;
    $inCode = false;
    $code = [];
    $flushParagraph = static function () use (&$paragraph, &$html) {
        if ($paragraph) {
            $html[] = '<p>' . implode('<br>', array_map('blog_inline', $paragraph)) . '</p>';
            $paragraph = [];
        }
    };
    $closeList = static function () use (&$list, &$html) {
        if ($list !== null) {
            $html[] = '</' . $list . '>';
            $list = null;
        }
    };

    foreach ($lines as $line) {
        if (preg_match('/^\s*```/', $line)) {
            $flushParagraph(); $closeList();
            if ($inCode) {
                $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $code), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
                $code = [];
            }
            $inCode = !$inCode;
            continue;
        }
        if ($inCode) { $code[] = $line; continue; }
        if (trim($line) === '') { $flushParagraph(); $closeList(); continue; }
        if (preg_match('/^\s*(?:-{3,}|\*{3,}|_{3,})\s*$/', $line)) {
            $flushParagraph(); $closeList(); $html[] = '<hr>';
            continue;
        }
        if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $m)) {
            $flushParagraph(); $closeList(); $level = strlen($m[1]);
            $html[] = '<h' . $level . '>' . blog_inline($m[2]) . '</h' . $level . '>';
            continue;
        }
        if (preg_match('/^>\s?(.*)$/', $line, $m)) {
            $flushParagraph(); $closeList();
            $html[] = '<blockquote><p>' . blog_inline($m[1]) . '</p></blockquote>';
            continue;
        }
        if (preg_match('/^\s*[-*+]\s+(.+)$/', $line, $m) || preg_match('/^\s*\d+\.\s+(.+)$/', $line, $m)) {
            $flushParagraph();
            $kind = preg_match('/^\s*\d+\./', $line) ? 'ol' : 'ul';
            if ($list !== $kind) { $closeList(); $list = $kind; $html[] = '<' . $list . '>'; }
            $html[] = '<li>' . blog_inline($m[1]) . '</li>';
            continue;
        }
        $closeList(); $paragraph[] = $line;
    }
    $flushParagraph(); $closeList();
    if ($inCode) {
        $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $code), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
    }
    return implode("\n", $html);
}

function blog_excerpt(string $markdown): string
{
    $plain = preg_replace('/[`*_>#\[\]()]/', '', $markdown) ?? $markdown;
    $plain = trim(preg_replace('/\s+/', ' ', $plain) ?? $plain);
    return preg_match('/^.{0,120}/us', $plain, $match) ? $match[0] : $plain;
}
