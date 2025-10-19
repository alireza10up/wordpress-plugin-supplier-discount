<?php

namespace XyzSupplierPlugin\Admin;

/**
 * Plugin settings management class
 *
 * @package XyzSupplierPlugin\Admin
 */
class Settings {
    const PAGE_SLUG = 'xyz-supplier-discount-settings';
    const OPTION_GROUP = 'xyz_supplier_discount_settings';
    
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings page to WooCommerce menu
     */
    public function add_settings_page() {
        // Check if user has permission
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        add_submenu_page(
            'woocommerce',
            __('Supplier Discount Settings', 'xyz-supplier-discount'),
            __('Supplier Discount', 'xyz-supplier-discount'),
            'manage_woocommerce',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Register setting
        register_setting(
            self::OPTION_GROUP,
            'xyzsp_apply_on_sale',
            [
                'type' => 'string',
                'default' => 'no',
                'sanitize_callback' => [$this, 'sanitize_yes_no'],
            ]
        );

        register_setting(
            self::OPTION_GROUP,
            'xyzsp_display_mode',
            [
                'type' => 'string',
                'default' => 'strikethrough',
                'sanitize_callback' => [$this, 'sanitize_display_mode'],
            ]
        );

        // Add settings section
        add_settings_section(
            'xyz_supplier_discount_general',
            __('General Settings', 'xyz-supplier-discount'),
            [$this, 'render_section_description'],
            self::PAGE_SLUG
        );

        // Add settings fields
        add_settings_field(
            'xyzsp_apply_on_sale',
            __('Apply on Sale Prices', 'xyz-supplier-discount'),
            [$this, 'render_apply_on_sale_field'],
            self::PAGE_SLUG,
            'xyz_supplier_discount_general'
        );

        add_settings_field(
            'xyzsp_display_mode',
            __('Display Mode', 'xyz-supplier-discount'),
            [$this, 'render_display_mode_field'],
            self::PAGE_SLUG,
            'xyz_supplier_discount_general'
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check if user has permission
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'xyz-supplier-discount'));
        }

        ?>
        <div class="wrap xyz-supplier-settings">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>

            <div class="xyz-supplier-info">
                <h3><?php esc_html_e('How to Use', 'xyz-supplier-discount'); ?></h3>
                <ol>
                    <li><?php esc_html_e('Create a user with "Supplier" role', 'xyz-supplier-discount'); ?></li>
                    <li><?php esc_html_e('Add discount percentage to products in the product edit page', 'xyz-supplier-discount'); ?></li>
                    <li><?php esc_html_e('When supplier users view products, they will see discounted prices', 'xyz-supplier-discount'); ?></li>
                </ol>
            </div>
        </div>
        <?php
    }

    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . esc_html__('Configure how supplier discounts are applied and displayed.', 'xyz-supplier-discount') . '</p>';
    }

    /**
     * Render apply on sale field
     */
    public function render_apply_on_sale_field() {
        $value = get_option('xyzsp_apply_on_sale', 'no');
        ?>
        <label>
            <input type="checkbox" name="xyzsp_apply_on_sale" value="yes" <?php checked($value, 'yes'); ?> />
            <?php esc_html_e('Apply supplier discount on sale prices instead of regular prices', 'xyz-supplier-discount'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('If checked, supplier discount will be applied to sale prices (if available) instead of regular prices.', 'xyz-supplier-discount'); ?>
        </p>
        <?php
    }

    /**
     * Render display mode field
     */
    public function render_display_mode_field() {
        $value = get_option('xyzsp_display_mode', 'strikethrough');
        ?>
        <select name="xyzsp_display_mode">
            <option value="strikethrough" <?php selected($value, 'strikethrough'); ?>>
                <?php esc_html_e('Strikethrough (show original price crossed out)', 'xyz-supplier-discount'); ?>
            </option>
            <option value="simple" <?php selected($value, 'simple'); ?>>
                <?php esc_html_e('Simple (show only discounted price)', 'xyz-supplier-discount'); ?>
            </option>
        </select>
        <p class="description">
            <?php esc_html_e('Choose how to display the discounted price to supplier users.', 'xyz-supplier-discount'); ?>
        </p>
        <?php
    }

    /**
     * Sanitize yes/no value
     *
     * @param string $value Input value
     * @return string
     */
    public function sanitize_yes_no($value) {
        return in_array($value, ['yes', 'no']) ? $value : 'no';
    }

    /**
     * Sanitize display mode value
     *
     * @param string $value Input value
     * @return string
     */
    public function sanitize_display_mode($value) {
        return in_array($value, ['strikethrough', 'simple']) ? $value : 'strikethrough';
    }
}
