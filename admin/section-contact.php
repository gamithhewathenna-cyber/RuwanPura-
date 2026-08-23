<?php
$page_title    = 'Contact: Page Content';
$section_group = 'contact';
$section_intro = 'The heading/intro text, the note above the form, and the map. Phone/Email/Address are managed under Home Page → Footer.';

// Accept either a plain URL or Google's full <iframe> "Embed a map" snippet —
// extract just the src URL either way, so we never store raw HTML/script tags
// (some hosts' security filters block POST bodies containing <iframe>/<script>).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['content']['contact_map_embed'])) {
    $raw = trim($_POST['content']['contact_map_embed']);
    if (stripos($raw, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $raw, $m)) {
        $_POST['content']['contact_map_embed'] = html_entity_decode($m[1]);
    } else {
        $_POST['content']['contact_map_embed'] = $raw;
    }
}

require __DIR__ . '/_section_editor.php';
