<?php

namespace XyzSupplierPlugin\Core;

/**
 * Plugin activation class
 *
 * @package XyzSupplierPlugin\Core
 */
class Activator {

    /**
     * Activate plugin
     */
    public static function activate() {
        // Add supplier role if it doesn't exist
        self::add_supplier_role();

        // Set default options
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Add supplier role
     */
    private static function add_supplier_role() {
        // Check if supplier role already exists
        if (get_role('supplier')) {
            return;
        }

        // Add supplier role
        add_role(
            'supplier',
            __('Supplier', 'xyz-supplier-discount'),
            [
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => true,
            ]
        );
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        // Default settings
        $default_options = [
            'xyzsp_apply_on_sale' => 'no',
            // TODO can use enums
            'xyzsp_display_mode' => 'strikethrough',
        ];

        foreach ($default_options as $option_name => $default_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $default_value);
            }
        }
    }
}
