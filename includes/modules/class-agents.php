<?php
namespace Converso\Modules;

class Agents {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_converso') return;

        wp_enqueue_media(); 

        wp_enqueue_script(
            'converso-agents-js',
            CONVERSO_PLUGIN_URL . "/assets/js/agents.js",
            ['jquery'],
            '1.0',
            true
        );

        // Optional CSS tweaks
        wp_enqueue_style(
            'converso-admin-css',
            CONVERSO_PLUGIN_URL . "/assets/css/agents.css",
            [],
            '1.0'
        );
    }

    public function register_settings() {
        register_setting(
            'converso_agents_settings',
            'converso_agents_data',
            [$this, 'sanitize_agents']
        );
    }

    public function sanitize_agents($input) {
        $default_index = isset($_POST['converso_agents_default']) ? intval($_POST['converso_agents_default']) : -1;

        foreach ($input as $index => &$agent) {
            $agent['name']  = sanitize_text_field($agent['name']);
            $agent['phone'] = sanitize_text_field($agent['phone']);
            $agent['greetings'] = sanitize_text_field($agent['greetings']);
            $agent['location'] = sanitize_text_field($agent['location']);
            $agent['photo'] = esc_url_raw($agent['photo']);
            $agent['default'] = ($index === $default_index);
            $agent['converso_agents_is_offline'] = sanitize_text_field($agent['converso_agents_is_offline']);
        }
        
        if (empty(get_settings_errors('converso_agents_settings'))) {
            add_settings_error(
                'converso_agents_settings',
                'settings_updated',
                __('Agents updated successfully.', 'converso'),
                'updated'
            );
        }

        return $input;
    }

    public function render() {
        $agents = get_option('converso_agents_data', []);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Agents Settings</h1>
            <div class="toast-placeholder">
                <?php settings_errors("converso_agents_settings") ?>
            </div>
            <form method="post" action="options.php">
                <?php settings_fields('converso_agents_settings'); ?>
                    <?php do_settings_sections('converso_agents_settings'); ?>   
                <table class="form-table" id="converso-agents-table">
                    <tr>
                        <td>
                            <table id="agents-repeater" style="width:100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="border:1px solid #ccc; padding:5px;width:10px">SN.</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Default</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Name</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Phone</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Photo</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Location</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Greetings</th>                                        
                                        <th style="border:1px solid #ccc; padding:5px;">Is Offline</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($agents)) : ?>
                                        <?php foreach ($agents as $index => $agent) : ?>
                                            <tr>
                                                <td style="" style="border:1px solid #ccc; padding:5px;"><?php echo $index + 1; ?></td>
                                                <td style=" border:1px solid #ccc; padding:5px; text-align:center;">
                                                    <input type="radio" name="converso_agents_default" value="<?php echo $index; ?>" <?php checked(!empty($agent['default'])); ?>>
                                                </td>
                                                <td style="border:1px solid #ccc; padding:5px;"><input type="text" name="converso_agents_data[<?php echo $index; ?>][name]" value="<?php echo esc_attr($agent['name']); ?>" class="regular-text"></td>
                                                <td style="border:1px solid #ccc; padding:5px;"><input type="text" name="converso_agents_data[<?php echo $index; ?>][phone]" value="<?php echo esc_attr($agent['phone']); ?>" class="regular-text"></td>                                               
                                                <td style="border:1px solid #ccc; padding:5px;height: 100%">
                                                    <div style="display: flex;align-items: center; gap: 10px">
                                                        <img src="<?php echo esc_attr($agent['photo'] ?? ''); ?>" style="max-width:50px; max-height:50px; display:block; margin-bottom:5px;" alt="">
                                                        <div>
                                                            <input type="text" name="converso_agents_data[<?php echo $index; ?>][photo]" value="<?php echo esc_attr($agent['photo'] ?? ''); ?>" class="regular-text agent-photo-url">
                                                            <button type="button" style="margin-top: 5px;" class="button select-photo">Select Image</button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="converso_location_parent">
                                                    <input type="text" 
                                                            name="converso_agents_data[<?php echo $index; ?>][location]" 
                                                            value="<?php echo esc_attr($agent['location']); ?>" 
                                                            class="converso_location" 
                                                            placeholder="Select location">                                                   
                                                </td>

                                                <td style="border:1px solid #ccc; padding:5px;">
                                                    <textarea name="converso_agents_data[<?php echo $index; ?>][greetings]" class="regular-text" rows="5"  id=""><?php echo esc_attr($agent['greetings']); ?></textarea>
                                                </td>
                                                <td style="border:1px solid #ccc; padding:5px; text-align:center;">
                                                    <input type="checkbox"
                                                        name="converso_agents_data[<?php echo $index; ?>][converso_agents_is_offline]"
                                                        value="1"
                                                        <?php checked(!empty($agent['converso_agents_is_offline'])); ?>>
                                                </td>

                                                <td style="border:1px solid #ccc; padding:5px;">
                                                    <button type="button" class="button remove-agent">Remove</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <button type="button" class="button button-secondary" id="add-agent" style="margin-top:10px;">Add Agent</button>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Changes'); ?>
            </form>
        </div>
        <?php
    }
}
