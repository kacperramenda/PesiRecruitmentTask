<?php
/**
 * Plugin Name: freeShippingCounter
 * Description: Display depending on your cart content how much you need for free shipping
 * Version: 1.0
 * Author: Kacper Ramenda
 */

namespace Pesi\FreeShippingCounter;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class Counter {
    const DEFAULT_THRESHOLD = 200.00;

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'loadAssets']);
        add_action( 'woocommerce_before_add_to_cart_button', [$this, 'show_counter'], 20 );
    }
    private function get_shipping_threshold() {
        return apply_filters( 'fsc_threshold', self::DEFAULT_THRESHOLD );
    }
    private function left_till_free_shipping($productPrice, $freeShippingCost) {
        return $freeShippingCost - $productPrice;
    }

    private function is_free_shipping($freeShippingCost, $productPrice): bool
    {
        if($productPrice>=$freeShippingCost) {
            return TRUE;
        }
        return FALSE;
    }

    private function get_icon_svg(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.4002 4.72716H2V16.8486H3.62851C3.82275 18.2191 4.98949 19.2728 6.40007 19.2728C7.81065 19.2728 8.97771 18.2191 9.17163 16.8486H14.8284C15.0223 18.2191 16.1893 19.2728 17.5999 19.2728C19.0105 19.2728 20.1776 18.2191 20.3715 16.8486H22V11.6651L18.3315 7.95953H16.3999L16.4002 4.72716ZM16.4002 9.57572V13.8884C16.7641 13.7138 17.1708 13.6162 17.6003 13.6162C18.7171 13.6162 19.6816 14.2769 20.1309 15.2324H20.4003V12.3349L17.6687 9.57572H16.4002ZM14.8002 15.2324V6.34335H3.60003V15.2324H3.86947C4.31876 14.2769 5.28293 13.6162 6.40007 13.6162C7.51721 13.6162 8.48138 14.2769 8.93067 15.2324H14.8002ZM17.6003 17.6567C16.9375 17.6567 16.4002 17.1139 16.4002 16.4445C16.4002 15.7751 16.9375 15.2324 17.6003 15.2324C18.263 15.2324 18.8003 15.7751 18.8003 16.4445C18.8003 17.1139 18.263 17.6567 17.6003 17.6567ZM7.60009 16.4445C7.60009 17.1139 7.0628 17.6567 6.40007 17.6567C5.73734 17.6567 5.20005 17.1139 5.20005 16.4445C5.20005 15.7751 5.73734 15.2324 6.40007 15.2324C7.0628 15.2324 7.60009 15.7751 7.60009 16.4445Z" fill="currentColor"/>
                </svg>';
    }

    private function get_checkmark_svg(): string
    {
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.79506 15.875L5.32506 12.405C4.93506 12.015 4.30506 12.015 3.91506 12.405C3.52506 12.795 3.52506 13.425 3.91506 13.815L8.09506 17.995C8.48506 18.385 9.11506 18.385 9.50506 17.995L20.0851 7.41499C20.4751 7.02499 20.4751 6.39499 20.0851 6.00499C19.6951 5.61499 19.0651 5.61499 18.6751 6.00499L8.79506 15.875Z" fill="#1ACB55"/>
                </svg>';
    }

    public function show_counter(): void
    {
        if ( ! function_exists( 'WC' )) {
            return;
        }

        global $product;

        if (!is_product() || !$product){
            return;
        }

        $freeShippingCost = $this->get_shipping_threshold();
        $productPrice = wc_get_price_to_display($product);
        $isFreeShipping = $this->is_free_shipping($freeShippingCost, $productPrice);
        $leftTillFreeShipping = $this->left_till_free_shipping($productPrice, $freeShippingCost);

        $freeShippingPercentage = $isFreeShipping ? 100 : 100 - ($leftTillFreeShipping/$freeShippingCost) * 100;

        $bgColor = $isFreeShipping ? 'bg-[#1ACB55]' : 'bg-[#FB6F05]';
        $textColor = $isFreeShipping ? 'text-[#1ACB55]' : 'text-[#FB6F05]';
        $shadow = $isFreeShipping ? 'shadow-[0px_2px_4px_0px_#1ACB551F]' : 'shadow-[0px_2px_4px_0px_#FB6F051F]';
        $text = $isFreeShipping ? '<p class="text-[14px]">Darmowa wysyłka dla tego produktu!</p>' :
            '<p class="text-[14px]">Brakuje Ci <span class="font-bold">'.wc_price($leftTillFreeShipping).'</span> do darmowej wysyłki!</p>';

        ?>
        <div class="flex flex-col gap-2 p-4 border border-[#EDEDED] rounded-2xl max-w-90 <?php echo esc_attr($shadow)?> my-8">
            <div class="h-1 w-full rounded-2xl bg-[#DBD7D7] relative">
                <div class="absolute left-0 top-0 h-1 <?php echo esc_attr($bgColor)?>" style="width: <?php echo esc_attr($freeShippingPercentage) ?>%;"></div>
            </div>
            <div class="flex gap-2 items-center">
                <span class="<?php echo esc_attr($textColor)?>">
                    <?php echo $this->get_icon_svg(); ?>
                </span>
                <?php echo wp_kses_post($text) ?>

                <?php if ($isFreeShipping): ?>
                    <?php echo $this->get_checkmark_svg(); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    public function loadAssets(): void
    {
        if (is_product())
        {
            $css_path = plugin_dir_path(__FILE__) . 'assets/css/style.css';
            $css_url = plugin_dir_url(__FILE__) . 'assets/css/style.css';

            if (file_exists($css_path)) {
                wp_enqueue_style('tailwind', $css_url, array(), filemtime($css_path));
            }
        }
    }
}

new Counter();