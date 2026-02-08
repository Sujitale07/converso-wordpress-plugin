<?php

namespace Connectapre\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        // Determine option group name. 'connectapre_general_settings' seems appropriate.
        // It was previously in General.php.
        
        $option_group = 'connectapre_general_settings';

        // Register existing and new settings
        register_setting($option_group, 'connectapre_business_name', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting($option_group, 'connectapre_business_type', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting($option_group, 'connectapre_cta_text', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting($option_group, 'connectapre_display_delay', ['sanitize_callback' => 'absint']);
        register_setting($option_group, 'connectapre_scroll_delay', ['sanitize_callback' => 'absint']);
        
        // New Settings
        register_setting($option_group, 'connectapre_enable_whatsapp', ['sanitize_callback' => 'sanitize_text_field']); // Mapped to "Enable Routing Engine"
        register_setting($option_group, 'connectapre_primary_number', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting($option_group, 'connectapre_fallback_primary_number', ['sanitize_callback' => 'sanitize_text_field']); // Boolean
        register_setting($option_group, 'connectapre_default_greeting', ['sanitize_callback' => 'sanitize_textarea_field']);
        register_setting($option_group, 'connectapre_offline_behavior', ['sanitize_callback' => 'sanitize_text_field']); // Boolean
        register_setting($option_group, 'connectapre_offline_message', ['sanitize_callback' => 'sanitize_textarea_field']);
    }

    public function render() {
        // Fetch options
        $enable_whatsapp = get_option('connectapre_enable_whatsapp', '1');
        $primary_number = get_option('connectapre_primary_number', '');
        $fallback_primary = get_option('connectapre_fallback_primary_number', '');
        $default_greeting = get_option('connectapre_default_greeting', '');
        $offline_behavior = get_option('connectapre_offline_behavior', '');
        $offline_message = get_option('connectapre_offline_message', '');
        $display_delay = get_option('connectapre_display_delay', '0');
        $scroll_delay = get_option('connectapre_scroll_delay', '0');

        ?>
        <div class="wrap relative !mt-5">
            <?php settings_errors('connectapre_general_settings'); ?>
            
            <form method="post" action="options.php" >
                <?php settings_fields('connectapre_general_settings'); ?>
                
                <div class="!bg-white !p-4 !px-6  !rounded">
                    <div class="flex justify-between">
                        <div class=" !mb-2">
                            <h3 class="font-primary !my-0 !text-xl"><?php esc_html_e('General Settings', 'connectapre'); ?></h3>
                            <p class="!mt-2 !text-sm text-gray-500"><?php esc_html_e('Configure global behaviors and site-wide WhatsApp settings', 'connectapre'); ?></p>
                        </div>

                        <div>
                            <button type="submit" class="bg-primary py-2 px-5 font-primary text-white rounded cursor-pointer !font-secondary" ><?php esc_html_e('Save Settings', 'connectapre'); ?></button>
                        </div>
                    </div>            
                    <div class="w-full h-[1px] bg-gray-200 !mb-6 "></div>
                    <div class="flex justify-between !gap-8 ">
                        <div class="w-1/2">
                            <h3 class="!text-base !mt-0 !mb-4  !font-secondary"><?php esc_html_e('Site Connection', 'connectapre'); ?></h3>
                            <div>
                                <label for="primary-number" class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Primary WhatsApp Number', 'connectapre'); ?></label>
                                <input type="text" name="connectapre_primary_number" value="<?php echo esc_attr($primary_number); ?>" placeholder="+1 123 456 7890" class="w-full rounded-lg !mt-3  !font-secondary !text-xs !pl-4 !py-3 !border !border-gray-200" id="primary-number">
                                <p class="!font-secondary !text-xs !text-gray-500 !mt-2"><?php esc_html_e('Default number used when no agent is available or routing is disabled', 'connectapre'); ?></p>
                            </div>
                            <div class="!mt-8">
                                <h3 class="!text-base !mt-0 !mb-4  !font-secondary"><?php esc_html_e('Default Experience', 'connectapre'); ?></h3>
                                <div >
                                    <label for="default-greeting" class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Global Greeting (fallback)', 'connectapre'); ?></label>
                                    <textarea name="connectapre_default_greeting" placeholder="Hii {customer_name}, thanks for reaching out to {site_name}. A member of our team will pick this up shortly"  class="w-full rounded-lg !mt-3  !font-secondary !text-xs !pl-4 !py-3 !border !border-gray-200" id="default-greeting"><?php echo esc_textarea($default_greeting); ?></textarea>
                                    <p class="!font-secondary !text-xs !text-gray-500 !mt-2"><?php esc_html_e('Message used when no specific agent greeting applies', 'connectapre'); ?></p>
                                </div>
                            </div>
                            <div class="!mt-8">
                                <h3 class="!text-base !mt-0 !mb-4  !font-secondary"><?php esc_html_e('Display Settings', 'connectapre'); ?></h3>
                                <div class="flex gap-4">
                                    <div class="w-1/2">
                                        <label for="display-delay" class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Display Delay (seconds)', 'connectapre'); ?></label>
                                        <input type="number" name="connectapre_display_delay" value="<?php echo esc_attr($display_delay); ?>" class="w-full rounded-lg !mt-3 !font-secondary !text-xs !pl-4 !py-3 !border !border-gray-200" min="0" id="display-delay">
                                    </div>
                                    <div class="w-1/2">
                                        <label for="scroll-delay" class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Scroll Delay (%)', 'connectapre'); ?></label>
                                        <input type="number" name="connectapre_scroll_delay" value="<?php echo esc_attr($scroll_delay); ?>" class="w-full rounded-lg !mt-3 !font-secondary !text-xs !pl-4 !py-3 !border !border-gray-200" min="0" max="100" id="scroll-delay">
                                    </div>
                                </div>
                                <p class="!font-secondary !text-xs !text-gray-500 !mt-2"><?php esc_html_e('Delayed display of the WhatsApp widget based on time or page scroll.', 'connectapre'); ?></p>
                            </div>
                        </div>
                        <div class="w-1/2">
                            <div>
                                <h3 class="!text-base !mt-0 !mb-4  !font-secondary"><?php esc_html_e('Status', 'connectapre'); ?></h3>
                                <div class="flex justify-between items-center">
                                    <p class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Enable Routing Engine', 'connectapre'); ?> <br> <?php esc_html_e('Turn on Connectapre logic for this site', 'connectapre'); ?></p>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="connectapre_enable_whatsapp" value="0">
                                            <input type="checkbox" name="connectapre_enable_whatsapp" value="1" class="sr-only peer" <?php checked('1', $enable_whatsapp); ?>>
                                            <div
                                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center !mt-5">
                                    <p class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Fallback to primary number', 'connectapre'); ?> <br> <?php esc_html_e('If no agent is online, send all chats to the primary number', 'connectapre'); ?></p>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="connectapre_fallback_primary_number" value="0">
                                            <input type="checkbox" name="connectapre_fallback_primary_number" value="1" class="sr-only peer" <?php checked('1', $fallback_primary); ?>>
                                            <div
                                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="!mt-6">
                                <h3 class="!text-base !mt-0 !mb-4  !font-secondary"><?php esc_html_e('Offline Behavior', 'connectapre'); ?></h3>
                                <div class="flex justify-between items-center">
                                    <p class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Show widget when all agents are offline', 'connectapre'); ?> <br><?php esc_html_e('If disabled, the widget is hidden outside working hours', 'connectapre'); ?></p>
                                    <div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="connectapre_offline_behavior" value="0">
                                            <input type="checkbox" name="connectapre_offline_behavior" value="1" class="sr-only peer" <?php checked('1', $offline_behavior); ?>>
                                            <div
                                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="!mt-4">
                                    <div >
                                        <label for="offline-message" class="!text-sm !my-0 text-gray-500 !font-secondary"><?php esc_html_e('Offline Message', 'connectapre'); ?></label>
                                        <textarea name="connectapre_offline_message" placeholder="Hii {customer_name}, thanks for reaching out to {site_name}. A member of our team will pick this up shortly"  class="w-full rounded-lg !mt-3  !font-secondary !text-xs !pl-4 !py-3 !border !border-gray-200" id="offline-message"><?php echo esc_textarea($offline_message); ?></textarea>
                                        <p class="!font-secondary !text-xs !text-gray-500 !mt-2"><?php esc_html_e('Shown in widget when nobody is available', 'connectapre'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div><?php
    }
}

