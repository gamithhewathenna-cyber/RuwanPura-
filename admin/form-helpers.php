<?php
/**
 * Reusable admin form-field renderers driven by content_blocks metadata.
 */
require_once __DIR__ . '/../includes/functions.php';

/* Fetch content blocks for a given group, ordered */
function content_group($group)
{
    $stmt = db()->prepare("SELECT * FROM content_blocks WHERE block_group = ? ORDER BY sort_order, id");
    $stmt->execute([$group]);
    return $stmt->fetchAll();
}

/* Render a single field based on its block_type */
function render_content_field($block)
{
    $key   = $block['block_key'];
    $label = $block['block_label'] ?: $key;
    $type  = $block['block_type'];
    $val   = $block['block_value'];

    echo '<div class="form-group">';
    echo '<label>' . e($label) . '</label>';

    switch ($type) {
        case 'textarea':
            echo '<textarea name="content[' . e($key) . ']" class="form-control" rows="3">' . e($val) . '</textarea>';
            break;

        case 'image':
            $previewId = 'prev_' . $key;
            echo '<div class="img-field">';
            echo '  <div class="img-preview" id="' . $previewId . '">';
            if ($val) {
                echo '<img src="' . UPLOAD_URL . e($val) . '">';
            } else {
                echo 'No image';
            }
            echo '  </div>';
            echo '  <div class="upload-btn-wrap">';
            echo '    <button type="button" class="btn btn-sm">Choose Image</button>';
            echo '    <input type="file" name="image_' . e($key) . '" accept="image/*" data-preview="' . $previewId . '">';
            echo '  </div>';
            echo '</div>';
            echo '<input type="hidden" name="image_keys[]" value="' . e($key) . '">';
            break;

        case 'link':
            echo '<input type="text" name="content[' . e($key) . ']" class="form-control" value="' . e($val) . '" placeholder="e.g. #contact or https://...">';
            break;

        default: // text
            echo '<input type="text" name="content[' . e($key) . ']" class="form-control" value="' . e($val) . '">';
    }
    echo '</div>';
}

/* Process a submitted content-group form: text + images */
function save_content_group()
{
    // Save text/textarea/link values
    if (!empty($_POST['content']) && is_array($_POST['content'])) {
        foreach ($_POST['content'] as $key => $value) {
            update_content($key, $value);
        }
    }
    // Save images (only those with an uploaded file)
    if (!empty($_POST['image_keys']) && is_array($_POST['image_keys'])) {
        foreach ($_POST['image_keys'] as $key) {
            $field = 'image_' . $key;
            $old   = c($key);
            $new   = handle_upload($field, $old);
            if ($new !== $old) {
                update_content($key, $new);
            }
        }
    }
}
