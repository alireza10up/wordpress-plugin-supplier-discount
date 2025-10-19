<?php

namespace XyzSupplierPlugin\Frontend;

/**
 * Price modification for supplier role
 *
 * @package XyzSupplierPlugin\Frontend
 */
class PriceModifier {
    const META_KEY = 'xyz_supplier_discount_percent';
    
    /**
     * Track processed products to prevent multiple discount applications
     * @var array
     */
    private static $processed_products = [];

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Only apply for supplier users
        if (!$this->is_supplier_user()) {
            return;
        }

        // Only modify the main price filters - this affects cart, checkout, emails, etc.
        add_filter('woocommerce_product_get_price', [$this, 'modify_product_price'], 10, 2);
        add_filter('woocommerce_product_variation_get_price', [$this, 'modify_product_price'], 10, 2);
        
        // Price display hook for HTML formatting
        add_filter('woocommerce_get_price_html', [$this, 'modify_price_html'], 20, 2);
    }

    /**
     * Check if current user is supplier
     *
     * @return bool
     */
    private function is_supplier_user() {
        return current_user_can('supplier') && !is_admin();
    }

    /**
     * Modify product price - this is the main price filter that affects everything
     *
     * @param float      $price   Product price
     * @param WC_Product $product Product object
     * @return float
     */
    public function modify_product_price($price, $product) {
        if (!$price || !$product) {
            return $price;
        }

        $product_id = $product->get_id();
        
        if (isset(self::$processed_products[$product_id])) {
            return self::$processed_products[$product_id];
        }

        $discount_percent = $this->get_discount_percent($product);
        if (!$discount_percent) {
            self::$processed_products[$product_id] = $price;
            return $price;
        }

        $apply_on_sale = get_option('xyzsp_apply_on_sale', 'no');
        $base_price = ($apply_on_sale === 'yes' && $product->get_sale_price()) 
                      ? $product->get_sale_price() 
                      : $product->get_regular_price();
        
        $discounted_price = $this->calculate_discounted_price($base_price, $discount_percent, $product);
        
        // Store the result to prevent multiple processing
        self::$processed_products[$product_id] = $discounted_price;
        
        return $discounted_price;
    }

    /**
     * Modify price HTML display
     *
     * @param string     $price_html Price HTML
     * @param WC_Product $product    Product object
     * @return string
     */
    public function modify_price_html($price_html, $product) {
        if (!$product) {
            return $price_html;
        }

        $discount_percent = $this->get_discount_percent($product);
        if (!$discount_percent) {
            return $price_html;
        }

        $apply_on_sale = get_option('xyzsp_apply_on_sale', 'no');
        $base_price = ($apply_on_sale === 'yes' && $product->get_sale_price()) 
                      ? $product->get_sale_price() 
                      : $product->get_regular_price();
        
        $discounted_price = $this->calculate_discounted_price($base_price, $discount_percent, $product);

        // Format the price HTML
        return $this->format_price_html($base_price, $discounted_price, $product);
    }

    /**
     * Get discount percentage for product
     *
     * @param WC_Product $product Product object
     * @return float|false
     */
    private function get_discount_percent($product) {
        if (!$product) {
            return false;
        }

        $discount_percent = get_post_meta($product->get_id(), self::META_KEY, true);
        
        if (empty($discount_percent)) {
            return false;
        }

        $discount_percent = floatval($discount_percent);
        
        return ($discount_percent > 0 && $discount_percent <= 100) ? $discount_percent : false;
    }

    /**
     * Calculate discounted price
     *
     * @param float      $price            Original price
     * @param float      $discount_percent Discount percentage
     * @param WC_Product $product          Product object
     * @return float
     */
    private function calculate_discounted_price($price, $discount_percent, $product) {
        $discount_amount = $price * ($discount_percent / 100);
        $discounted_price = $price - $discount_amount;

        // Ensure price is not negative
        return max(0, $discounted_price);
    }

    /**
     * Format price HTML based on display mode
     *
     * @param float      $original_price   Original price
     * @param float      $discounted_price Discounted price
     * @param WC_Product $product          Product object
     * @return string
     */
    private function format_price_html($original_price, $discounted_price, $product) {
        $display_mode = get_option('xyzsp_display_mode', 'strikethrough');
        
        $original_price_html = wc_price($original_price);
        $discounted_price_html = wc_price($discounted_price);

        if ($display_mode === 'simple') {
            return $discounted_price_html;
        }

        // Strikethrough mode
        return sprintf(
            '<del>%s</del> <ins>%s</ins>',
            $original_price_html,
            $discounted_price_html
        );
    }
}
