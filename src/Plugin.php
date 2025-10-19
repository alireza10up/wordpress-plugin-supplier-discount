<?php

namespace XyzSupplierPlugin;

/**
 * Main plugin class
 *
 * @package XyzSupplierPlugin
 */
class Plugin {
    private static $instance = null;
    public $version = XYZ_SUPPLIER_DISCOUNT_VERSION;
    public $plugin_dir;
    public $plugin_url;

    /**
     * Get plugin instance (singleton)
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_dir = XYZ_SUPPLIER_DISCOUNT_PLUGIN_DIR;
        $this->plugin_url = XYZ_SUPPLIER_DISCOUNT_PLUGIN_URL;

        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(XYZ_SUPPLIER_DISCOUNT_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(XYZ_SUPPLIER_DISCOUNT_PLUGIN_FILE, [$this, 'deactivate']);

        // Initialize plugin
        add_action('init', [$this, 'init']);
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load admin classes
        if (is_admin()) {
            $this->load_admin_classes();
        }

        // Load frontend classes
        if (!is_admin() || wp_doing_ajax()) {
            $this->load_frontend_classes();
        }
    }

    /**
     * Load admin classes
     */
    private function load_admin_classes() {
        new Admin\ProductFields();
        new Admin\Settings();
    }

    /**
     * Load frontend classes
     */
    private function load_frontend_classes() {
        new Frontend\PriceModifier();
    }

    /**
     * Activate plugin
     */
    public function activate() {
        Core\Activator::activate();
    }

    /**
     * Deactivate plugin
     */
    public function deactivate() {
        Core\Deactivator::deactivate();
    }
}
