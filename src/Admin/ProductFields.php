<?php

namespace XyzSupplierPlugin\Admin;

/**
 * Product fields management class
 *
 * @package XyzSupplierPlugin\Admin
 */
class ProductFields {
    const META_KEY = 'xyz_supplier_discount_percent';

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Simple products
        add_action('woocommerce_product_options_pricing', [$this, 'add_simple_product_field']);
        add_action('woocommerce_process_product_meta', [$this, 'save_simple_product_field']);

        // Variable products
        add_action('woocommerce_variation_options_pricing', [$this, 'add_variation_field'], 10, 3);
        add_action('woocommerce_save_product_variation', [$this, 'save_variation_field'], 10, 2);

        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    /**
     * Add supplier discount field to simple products
     */
    public function add_simple_product_field() {
        global $post;

        $value = get_post_meta($post->ID, self::META_KEY, true);
        $value = $value ? $value : '';

        echo '<div class="options_group">';
        woocommerce_wp_text_input([
            'id' => self::META_KEY,
            'label' => __('Supplier Discount %', 'xyz-supplier-discount'),
            'description' => __('Enter discount percentage for supplier role (1-100)', 'xyz-supplier-discount'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => [
                'min' => '1',
                'max' => '100',
                'step' => '1',
            ],
            'value' => $value,
        ]);
        echo '</div>';
    }

    /**
     * Save supplier discount field for simple products
     *
     * @param int $post_id Post ID
     */
    public function save_simple_product_field($post_id) {
        // Security checks
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Get and validate the value
        $discount_percent = isset($_POST[self::META_KEY]) ? sanitize_text_field($_POST[self::META_KEY]) : '';

        if ($this->validate_discount_percent($discount_percent)) {
            update_post_meta($post_id, self::META_KEY, $discount_percent);
        } else {
            delete_post_meta($post_id, self::META_KEY);
        }
    }

    /**
     * Add supplier discount field to product variations
     *
     * @param int     $loop           Variation loop counter
     * @param array   $variation_data Variation data
     * @param WP_Post $variation      Variation post object
     */
    public function add_variation_field($loop, $variation_data, $variation) {
        $value = get_post_meta($variation->ID, self::META_KEY, true);
        $value = $value ? $value : '';

        echo '<div class="form-row form-row-full">';
        woocommerce_wp_text_input([
            'id' => self::META_KEY . '[' . $loop . ']',
            'name' => self::META_KEY . '[' . $loop . ']',
            'label' => __('Supplier Discount %', 'xyz-supplier-discount'),
            'description' => __('Enter discount percentage for supplier role (1-100)', 'xyz-supplier-discount'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => [
                'min' => '1',
                'max' => '100',
                'step' => '1',
            ],
            'value' => $value,
            'wrapper_class' => 'form-row form-row-full',
        ]);
        echo '</div>';
    }

    /**
     * Save supplier discount field for product variations
     *
     * @param int $variation_id Variation ID
     * @param int $loop         Variation loop counter
     */
    public function save_variation_field($variation_id, $loop) {
        // Security checks
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (!isset($_POST['variable_post_id'])) {
            return;
        }

        // Get and validate the value
        $discount_percent = isset($_POST[self::META_KEY][$loop]) ? sanitize_text_field($_POST[self::META_KEY][$loop]) : '';

        if ($this->validate_discount_percent($discount_percent)) {
            update_post_meta($variation_id, self::META_KEY, $discount_percent);
        } else {
            delete_post_meta($variation_id, self::META_KEY);
        }
    }

    /**
     * Validate discount percentage
     *
     * @param string $value Input value
     * @return bool
     */
    private function validate_discount_percent($value) {
        if (empty($value)) {
            return true; // Allow empty values
        }

        // Check if it's a valid integer
        if (!is_numeric($value) || !is_int((int)$value) || (int)$value != (float)$value) {
            return false;
        }

        $value = (int)$value;
        return $value >= 1 && $value <= 100;
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts() {
        $screen = get_current_screen();
        
        if (!$screen || !in_array($screen->id, ['product', 'edit-product'])) {
            return;
        }

        wp_enqueue_script(
            'xyz-supplier-discount-admin',
            XYZ_SUPPLIER_DISCOUNT_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            XYZ_SUPPLIER_DISCOUNT_VERSION,
            true
        );

        wp_enqueue_style(
            'xyz-supplier-discount-admin',
            XYZ_SUPPLIER_DISCOUNT_PLUGIN_URL . 'assets/css/admin.css',
            [],
            XYZ_SUPPLIER_DISCOUNT_VERSION
        );
    }
}
