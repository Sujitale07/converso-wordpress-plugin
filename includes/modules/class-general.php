<?php
namespace Converso\Modules;

class General {

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('converso_general_settings', 'converso_business_name', [
            'sanitize_callback' => [$this, 'sanitize_general_settings']
        ]);

        register_setting('converso_general_settings', 'converso_business_type');
        register_setting('converso_general_settings', 'converso_cta_text');
        register_setting('converso_general_settings', 'converso_display_delay');
        register_setting('converso_general_settings', 'converso_scroll_delay');
        register_setting('converso_general_settings', 'converso_hide_when_offline');
        register_setting('converso_general_settings', 'converso_enable_whatsapp');
    }

    public function sanitize_general_settings($value) {
        $value = sanitize_text_field($value);

        add_settings_error(
            'converso_general_settings',
            'general_settings_updated',
            __('General settings updated successfully.', 'converso'),
            'updated'
        );

        return $value;
    }


    public function render() {
        // Fetch saved values
        $business_name = get_option('converso_business_name', '');
        $business_type = get_option('converso_business_type', '');
        $cta_text = get_option('converso_cta_text', '');
        $display_delay = get_option('converso_display_delay', '');
        $scroll_delay = get_option('converso_scroll_delay', '');
        $enable_whatsapp = get_option('converso_enable_whatsapp', 0);
        $converso_hide_when_offline = get_option('converso_hide_when_offline', '');
        ?>

        <div class="wrap">
            <h1 class="wp-heading-inline">General Settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields('converso_general_settings'); ?>
                <?php do_settings_sections('converso_general_settings'); ?>
                <?php settings_errors('converso_general_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="converso_business_name">Business Name</label></th>
                        <td><input name="converso_business_name" type="text" id="converso_business_name" value="<?php echo esc_attr($business_name); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="converso_business_type">Business Type</label></th>
                        <td>
                            <select name="converso_business_type" id="converso_business_type">
                                <option value="">Select Type</option>
                                <option value="retail" <?php selected($business_type, 'retail'); ?>>Retail</option>
                                <option value="service" <?php selected($business_type, 'service'); ?>>Service</option>
                                <option value="restaurant" <?php selected($business_type, 'restaurant'); ?>>Restaurant</option>
                                <option value="education" <?php selected($business_type, 'education'); ?>>Education</option>
                                <option value="other" <?php selected($business_type, 'other'); ?>>Other</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="converso_cta_text">CTA Text</label></th>
                        <td>
                            <input name="converso_cta_text" type="text" id="converso_cta_text" value="<?php echo esc_attr($cta_text); ?>" class="regular-text">
                            <p class="description">Example: "Contact us now", "Book a Demo"</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="converso_display_delay">Display After Time Delay (seconds)</label></th>
                        <td>
                            <input name="converso_display_delay" type="number" id="converso_display_delay" value="<?php echo esc_attr($display_delay); ?>" class="regular-text">
                            <p class="description">Time after page load to show CTA</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="converso_scroll_delay">Scroll Delay (%)</label></th>
                        <td>
                            <input name="converso_scroll_delay" type="number" id="converso_scroll_delay" value="<?php echo esc_attr($scroll_delay); ?>" class="regular-text">
                            <p class="description">Percentage of scroll after which CTA appears</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Hide When Offline</th>
                        <td>
                            <label>
                                <input type="checkbox" name="converso_hide_when_offline" value="1" <?php checked($converso_hide_when_offline, 1); ?>>
                                <span class="description">Hide WhatsApp While Agent is Offline</span>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Enable WhatsApp CTA</th>
                        <td>
                            <label>
                                <input type="checkbox" name="converso_enable_whatsapp" value="1" <?php checked($enable_whatsapp, 1); ?>>
                                <span class="description">Show WhatsApp button on frontend</span>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Changes'); ?>
            </form>
        </div>


        <?php
    }
}
