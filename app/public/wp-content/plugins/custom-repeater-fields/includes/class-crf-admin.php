<?php
/**
 * Admin class — meta box registration, render, save
 * File: includes/class-crf-admin.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class CRF_Admin
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'register_meta_box'));
        add_action('save_post_product', array($this, 'save_meta'));
        add_filter('safe_style_css', array($this, 'allow_list_style_css'));
    }

    public function allow_list_style_css($styles)
    {
        $styles[] = 'list-style';
        return $styles;
    }

    public function register_meta_box()
    {
        add_meta_box(
            'crf_product_dropdowns',
            'Product Dropdowns',
            array($this, 'render_meta_box'),
            'product',
            'normal',
            'default'
        );
    }

    public function render_meta_box($post)
    {
        $dropdowns = get_post_meta($post->ID, 'crf_product_dropdowns', true) ?: [];
        wp_nonce_field('crf_product_dropdowns_nonce', 'crf_product_dropdowns_nonce');
        ?>
        <div id="crf-product-dropdowns-wrapper">
            <?php foreach ($dropdowns as $i => $row): ?>
                <div class="crf-dropdown-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">
                    <p>
                        <label><strong>Title</strong></label><br>
                        <input type="text" name="crf_product_dropdowns[<?= $i ?>][title]"
                               value="<?= esc_attr($row['title'] ?? '') ?>" style="width:100%">
                    </p>
                    <p>
                        <label><strong>Content</strong></label><br>
                        <textarea name="crf_product_dropdowns[<?= $i ?>][content]"
                                  rows="5" style="width:100%"><?= esc_textarea($row['content'] ?? '') ?></textarea>
                    </p>
                    <button type="button" class="crf-remove-dropdown-row button">Remove</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="crf-add-dropdown-row" class="button button-primary">+ Add Row</button>

        <script>
        (function($) {
            var index = <?= count($dropdowns) ?>;

            $('#crf-add-dropdown-row').on('click', function() {
                $('#crf-product-dropdowns-wrapper').append(
                    '<div class="crf-dropdown-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">' +
                        '<p><label><strong>Title</strong></label><br>' +
                        '<input type="text" name="crf_product_dropdowns[' + index + '][title]" style="width:100%"></p>' +
                        '<p><label><strong>Content</strong></label><br>' +
                        '<textarea name="crf_product_dropdowns[' + index + '][content]" rows="5" style="width:100%"></textarea></p>' +
                        '<button type="button" class="crf-remove-dropdown-row button">Remove</button>' +
                    '</div>'
                );
                index++;
            });

            $(document).on('click', '.crf-remove-dropdown-row', function() {
                $(this).closest('.crf-dropdown-row').remove();
            });
        })(jQuery);
        </script>
        <?php
    }

    public function save_meta($post_id)
    {
        if (!isset($_POST['crf_product_dropdowns_nonce'])) return;
        if (!wp_verify_nonce($_POST['crf_product_dropdowns_nonce'], 'crf_product_dropdowns_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $dropdowns = [];
        if (!empty($_POST['crf_product_dropdowns']) && is_array($_POST['crf_product_dropdowns'])) {
            foreach ($_POST['crf_product_dropdowns'] as $row) {
                $dropdowns[] = [
                    'title'   => sanitize_text_field($row['title']   ?? ''),
                    'content' => wp_kses_post($row['content'] ?? ''),
                ];
            }
        }

        update_post_meta($post_id, 'crf_product_dropdowns', $dropdowns);
    }
}
