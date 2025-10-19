<?php

namespace XyzSupplierPlugin\Frontend;

/**
 * Price modification for supplier role
 *
 * @package XyzSupplierPlugin\Frontend
 */
class PriceModifier {
    const META_KEY = 'xyz_supplier_discount_percent';

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

        // Price modification hooks
        add_filter('woocommerce_product_get_price', [$this, 'modify_product_price'], 10, 2);
        add_filter('woocommerce_product_get_sale_price', [$this, 'modify_sale_price'], 10, 2);
        add_filter('woocommerce_product_variation_get_price', [$this, 'modify_variation_price'], 10, 2);
        add_filter('woocommerce_product_variation_get_sale_price', [$this, 'modify_variation_sale_price'], 10, 2);

        // Price display hooks
        add_filter('woocommerce_get_price_html', [$this, 'modify_price_html'], 10, 2);
        add_filter('woocommerce_variation_prices', [$this, 'modify_variation_prices'], 10, 3);
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
     * Modify product price
     *
     * @param float      $price   Product price
     * @param WC_Product $product Product object
     * @return float
     */
    public function modify_product_price($price, $product) {
        if (!$price || !$product) {
            return $price;
        }

        $discount_percent = $this->get_discount_percent($product);
        if (!$discount_percent) {
            return $price;
        }

        return $this->calculate_discounted_price($price, $discount_percent, $product);
    }

    /**
     * Modify sale price
     *
     * @param float      $price   Sale price
     * @param WC_Product $product Product object
     * @return float
     */
    public function modify_sale_price($price, $product) {
        if (!$price || !$product) {
            return $price;
        }

        $discount_percent = $this->get_discount_percent($product);
        if (!$discount_percent) {
            return $price;
        }

        // Check if we should apply discount on sale prices
        $apply_on_sale = get_option('xyzsp_apply_on_sale', 'no');
        if ($apply_on_sale !== 'yes') {
            return $price;
        }

        return $this->calculate_discounted_price($price, $discount_percent, $product);
    }

    /**
     * Modify variation price
     *
     * @param float      $price   Variation price
     * @param WC_Product $product Variation object
     * @return float
     */
    public function modify_variation_price($price, $product) {
        if (!$price || !$product) {
            return $price;
        }

        $discount_percent = $this->get_discount_percent($product);
        if (!$discount_percent) {
            return $price;
        }

        return $this->calculate_discounted_price($price, $discount_percent, $product);
    }

    /**
     * Modify variation sale price
     *
     * @param float      $price   Variation sale price
     * @param WC_Product $product Variation object
     * @return float
     */
    public function modify_variation_sale_price($price, $product) {
        if (!$price || !$product) {
            return $price;
        }

        $discount_percent = $this->get_discount_percent($product);
        if (!$discount_percent) {
            return $price;
        }

        // Check if we should apply discount on sale prices
        $apply_on_sale = get_option('xyzsp_apply_on_sale', 'no');
        if ($apply_on_sale !== 'yes') {
            return $price;
        }

        return $this->calculate_discounted_price($price, $discount_percent, $product);
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

        $original_price = $this->get_original_price($product);
        $discounted_price = $this->calculate_discounted_price($original_price, $discount_percent, $product);

        if ($discounted_price >= $original_price) {
            return $price_html;
        }

        return $this->format_price_html($original_price, $discounted_price, $product);
    }

    /**
     * Modify variation prices array
     *
     * @param array      $prices    Prices array
     * @param WC_Product $product   Product object
     * @param bool       $for_display For display flag
     * @return array
     */
    public function modify_variation_prices($prices, $product, $for_display) {
        if (!$for_display || !$product) {
            return $prices;
        }

        $variation_ids = array_keys($prices['price']);
        $modified_prices = $prices;

        foreach ($variation_ids as $variation_id) {
            $variation = wc_get_product($variation_id);
            if (!$variation) {
                continue;
            }

            $discount_percent = $this->get_discount_percent($variation);
            if (!$discount_percent) {
                continue;
            }

            $original_price = $variation->get_regular_price();
            $discounted_price = $this->calculate_discounted_price($original_price, $discount_percent, $variation);

            if ($discounted_price < $original_price) {
                $modified_prices['price'][$variation_id] = $discounted_price;
            }
        }

        return $modified_prices;
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
     * Get original price for display
     *
     * @param WC_Product $product Product object
     * @return float
     */
    private function get_original_price($product) {
        $apply_on_sale = get_option('xyzsp_apply_on_sale', 'no');
        
        if ($apply_on_sale === 'yes' && $product->get_sale_price()) {
            return $product->get_sale_price();
        }

        return $product->get_regular_price();
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
