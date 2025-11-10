<?php

namespace Converso\Modules;

class DynamicFields{
    public function __construct(){
        add_action("admin_enqueue_scripts", [$this, 'enqueue_assets']);
        add_action("admin_init", [$this, "register_settings"]);
    }

    public function register_settings(){
        register_setting(
            'converso_dynamic_fields_settings',
            'converso_dynamic_fields_data'   ,
            [$this, 'sanitize_fields']
        );
    }

    public function sanitize_fields($input) {
        $clean_fields = [];
        $callables = [];

        foreach ($input as $index => $field) {
            $name = sanitize_text_field($field['name'] ?? '');
            $value = sanitize_text_field($field['value'] ?? '');
            $callable = sanitize_text_field($field['callable'] ?? '');

            if (empty($name)) {
                continue;
            }
            
            if (!empty($name) && empty($value)) {
                add_settings_error(
                    'converso_dynamic_fields_settings',
                    'empty_value_' . $index,
                    sprintf(__('Value cannot be empty for field "%s".', 'converso'), $name),
                    'error'
                );
                continue;
            }

            if (in_array($callable, $callables)) {
                add_settings_error(
                    'converso_dynamic_fields_settings',
                    'duplicate_callable_' . $index,
                    sprintf(__('Duplicate callable "%s" found. Each callable must be unique.', 'converso'), $callable),
                    'error'
                );
                continue;
            }

            $callables[] = $callable;

            $clean_fields[] = [
                'name' => $name,
                'value' => $value,
                'callable' => $callable
            ];
        }

        if (empty(get_settings_errors('converso_dynamic_fields_settings'))) {
            add_settings_error(
                'converso_dynamic_fields_settings',
                'settings_updated',
                __('Dynamic fields updated successfully.', 'converso'),
                'updated'
            );
        }

        return $clean_fields;
    }



    public function enqueue_assets($hook){
        if ($hook !== 'toplevel_page_converso') return; 

        wp_enqueue_script(
            'converso-dynamic-fields-js',
            CONVERSO_PLUGIN_URL . "/assets/js/dynamic-fields.js",
            ['jquery'],
            '1.0',
            true
        );
    }
    
    public function render(){

        $dynamic_fields = get_option('converso_dynamic_fields_data', []);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Dynamic Fields</h1>
            <div class="toast-placeholder">
                <?php settings_errors("converso_dynamic_fields_settings") ?>
            </div>
            <form method="post" action="options.php">
                <?php settings_fields('converso_dynamic_fields_settings'); ?>
                <?php do_settings_sections('converso_dynamic_fields_settings'); ?>

                <table class="form-table" id="converso-dynamic-fields-table">
                    <tr>
                        <td>
                            <table id="dynamic-fields-repeater" style="width:100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="border:1px solid #ccc; padding:5px;width: 10px">SN.</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Name</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Value</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Callable</th>
                                        <th style="border:1px solid #ccc; padding:5px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($dynamic_fields)) : ?>
                                        <?php foreach ($dynamic_fields as $index => $field) : ?>
                                            <tr>
                                                <td style="border:1px solid #ccc; padding:5px;"><?php echo $index + 1; ?></td>
                                                <td style="border:1px solid #ccc; padding:5px;"><input type="text" name="converso_dynamic_fields_data[<?php echo $index; ?>][name]" value="<?php echo esc_attr($field['name']); ?>" class="regular-text"></td>
                                                <td style="border:1px solid #ccc; padding:5px;"><input type="text" name="converso_dynamic_fields_data[<?php echo $index; ?>][value]" value="<?php echo esc_attr($field['value']); ?>" class="regular-text"></td>
                                                <td style="border:1px solid #ccc; padding:5px;"><input readonly type="text" name="converso_dynamic_fields_data[<?php echo $index; ?>][callable]" value="<?php echo esc_attr($field['callable']); ?>" class="regular-text"></td>
                                                <td style="border:1px solid #ccc; padding:5px;">
                                                    <button type="button" class="button remove-dynamic-fields">Remove</button>
                                                </td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <button type="button" class="button button-secondary" id="add-dynamic-fields" style="margin-top:10px;">Add Fields</button>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Changes'); ?>
            </form>
        <?php
    }
}