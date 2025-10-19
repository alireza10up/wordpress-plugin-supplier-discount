<?php

namespace XyzSupplierPlugin\Core;

/**
 * Plugin deactivation class
 *
 * @package XyzSupplierPlugin\Core
 */
class Deactivator {

    /**
     * Deactivate plugin
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
