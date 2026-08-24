<?php
$page_title    = 'About: Direct From The Source';
$section_group = 'about_video';
$section_intro = 'The dark video section and the social & environmental responsibility text below it.';

// Accept either a plain video URL or a full <iframe> embed snippet (e.g. YouTube's
// "Embed" code) — extract just the src URL either way, so we never store raw HTML
// (some hosts' security filters block POST bodies containing <iframe>/<script>).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content']['video_url'])) {
    $raw = trim($_POST['content']['video_url']);
    if (stripos($raw, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $raw, $m)) {
        $_POST['content']['video_url'] = html_entity_decode($m[1]);
    } else {
        $_POST['content']['video_url'] = $raw;
    }
}

require __DIR__ . '/_section_editor.php';
