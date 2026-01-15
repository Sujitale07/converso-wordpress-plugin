<?php

namespace Converso\Modules;

use Converso\Services\DynamicFieldsService;
use Converso\Core\Notification;

class DynamicFields {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        add_action('admin_init', [$this, 'handle_add_field']);
        add_action('admin_init', [$this, 'handle_edit_field']);
        add_action('admin_init', [$this, 'handle_delete_field']);
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_converso') return;

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        if ($tab !== 'dynamic-fields') return;

        wp_enqueue_script(
            'converso-dynamic-fields-js',
            CONVERSO_PLUGIN_URL . "/assets/js/dynamic-fields.js",
            ['jquery'],
            '1.0',
            true
        );
        wp_enqueue_style(
            'converso-dynamic-fields-css',
            CONVERSO_PLUGIN_URL . "/assets/css/dynamic-fields.css",
            ['jquery'],
            '1.0',
            true
        );

        // Uses same CSS as agents or global Tailwind
    }

    public function handle_add_field() {
        if (!isset($_POST['converso_action']) || $_POST['converso_action'] !== 'add_field') {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized request', 'converso'));
        }

        check_admin_referer('converso_dynamic_fields');

        $name = sanitize_text_field($_POST['name'] ?? '');
        $value = sanitize_text_field($_POST['value'] ?? '');
        $callable = sanitize_text_field($_POST['callable'] ?? '');

        if (empty($name) || empty($value)) {
            Notification::error('Name and Value are required.', 'Error');
            wp_redirect(add_query_arg([], admin_url('admin.php?page=converso&tab=dynamic-fields')));
            exit;
        }

        $result = DynamicFieldsService::create_field([
            'name' => $name,
            'value' => $value,
            'callable' => $callable,
            'created_at' => current_time('mysql')
        ]);

        if (is_wp_error($result)) {
            Notification::error($result->get_error_message(), 'Error');
        } else {
            Notification::success('Field added successfully!', 'Success');
        }

        wp_redirect(add_query_arg([], admin_url('admin.php?page=converso&tab=dynamic-fields')));
        exit;
    }

    public function handle_edit_field() {
        if (!isset($_POST['converso_action']) || $_POST['converso_action'] !== 'edit_field') {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized request', 'converso'));
        }

        check_admin_referer('converso_dynamic_fields');

        $field_id = sanitize_text_field($_POST['field_id'] ?? '');
        $name = sanitize_text_field($_POST['edit--name'] ?? '');
        $value = sanitize_text_field($_POST['edit--value'] ?? '');
        $callable = sanitize_text_field($_POST['edit--callable'] ?? '');

        if (empty($field_id) || empty($name) || empty($value) || empty($callable)) {
             Notification::error('All fields are required.', 'Error');
             wp_redirect(add_query_arg([], admin_url('admin.php?page=converso&tab=dynamic-fields')));
             exit;
        }

        $result = DynamicFieldsService::update_field($field_id, [
            'name' => $name,
            'value' => $value,
            'callable' => $callable,
            'updated_at' => current_time('mysql')
        ]);

        if (is_wp_error($result)) {
            Notification::error($result->get_error_message(), 'Error');
        } else {
            Notification::success('Field updated successfully!', 'Success');
        }
        
        wp_redirect(add_query_arg([], admin_url('admin.php?page=converso&tab=dynamic-fields')));
        exit;
    }

    public function handle_delete_field() {
        if (!isset($_POST['converso_action']) || $_POST['converso_action'] !== 'delete_field') {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized request', 'converso'));
        }

        check_admin_referer('converso_delete_field');

        $field_id = sanitize_text_field($_POST['field_id'] ?? '');

        if (empty($field_id)) {
            Notification::error('Invalid field ID.', 'Error');
            wp_redirect(add_query_arg([], admin_url('admin.php?page=converso&tab=dynamic-fields')));
            exit;
        }

        DynamicFieldsService::delete_field($field_id);

        Notification::success('Field deleted successfully!', 'Deleted');
        wp_redirect(add_query_arg([], admin_url('admin.php?page=converso&tab=dynamic-fields')));
        exit;
    }

    public function render() {
        $filters = [];
        $filters['search'] = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $filters['sort']   = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
        $filters['page']   = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $filters['limit']  = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 10;

        $data = DynamicFieldsService::get_fields($filters);
        
        $fields = $data['fields'];
        $total_pages = $data['total_pages'];
        $limit = $data['limit'];
        $search = $data['search'];
        $page = $data['page'];
        ?>
        <div class="wrap relative !mt-5 !bg-white !p-4 !px-6 !rounded">
            <div class=" flex justify-between items-center">
                <div>
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Dynamic Fields</h3>
                    <p class="!mt-2 !font-primary !text-sm !text-gray-500">Manage custom placeholders to personalize your WhatsApp messages</p>
                </div>

                <div>
                    <button onclick="openFieldModal()" class="bg-primary py-2 px-5 font-primary text-white rounded cursor-pointer"
                        id="add-field"><i class="ri-add-large-line mr-2"></i> Add Field</button>
                </div>
            </div>

            <div class="flex justify-between items-end gap-5 mt-5">
                <div class="w-3/5">
                    <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Search</label>
                    <div class="relative !mt-2">
                        <div class="absolute top-[53%] left-4 -translate-y-1/2">
                            <i class="ri-search-line"></i>
                        </div>
                        <input type="text" id="field-search" value="<?php echo esc_attr($search); ?>" placeholder="Search by name or value"
                            class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-8 !py-3 !bg-gray-200 !border-none">
                    </div>
                </div>
                
                <div class="w-1/5">
                    <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Sort By</label>
                    <div class="relative !mt-2">
                        <div class="absolute top-[53%] left-4 -translate-y-1/2">
                            <i class="ri-equalizer-2-line"></i>
                        </div>
                        <select name=""
                            class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-10 !py-3 !bg-gray-200 !border-none"
                            id="field-sort">
                            <option value="">Newest</option>
                            <option value="oldest" <?php selected($filters['sort'], 'oldest'); ?>>Oldest</option>
                            <option value="name" <?php selected($filters['sort'], 'name'); ?>>Name</option>
                        </select>
                    </div>
                </div>    
                <div class="w-1/5">        
                    <button class="bg-gray-800 w-full py-2 px-5 !font-primary text-white rounded cursor-pointer" id="apply-filter"> Apply Filter</button>
                </div>
            </div>

            <div id="modalBackdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
                <div id="addFieldModal" class="add-field-modal  opacity-0 scale-90 !px-6 !py-5 !max-h-[70%] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out overflow-y-scroll bg-white rounded-lg shadow-xl w-[500px]">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">Add New Field</h2>
                        <button onclick="closeFieldModal()" class="text-lg">✖</button>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=converso&tab=dynamic-fields')); ?>" class="!mt-4" id="fieldForm">
                        <?php wp_nonce_field('converso_dynamic_fields'); ?>
                        <input type="hidden" name="converso_action" value="add_field" />

                        <div class="mb-5">
                            
                            <label class="block text-sm font-medium text-gray-700 mb-2">Field Name<span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="add--name" placeholder="Eg. Order ID" class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none" required />
                            <p class="text-xs text-gray-500 mt-1">This is the label shown to the user or used internally.</p>
                        </div>
                        
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Callable</label>
                            <input type="text" name="callable" id="add--callable" placeholder="Eg. {order_id}" class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none" />
                            <p class="text-xs text-gray-500 mt-1">Unique identifier. Auto-generated if left empty.</p>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Value / Placeholder<span class="text-red-500">*</span></label>
                            <input type="text" name="value" placeholder="Eg. {{order_id}}" class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none" required />
                            <p class="text-xs text-gray-500 mt-1">The value of the field. Can be a static text or variable.</p>
                        </div>

                        <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all">Save Field</button>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="edit-field-modal-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
                <div id="edit-field-modal" class="add-field-modal opacity-0 scale-90 !px-6 !py-5 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out overflow-y-scroll max-h-[70%] bg-white rounded-lg shadow-xl w-[500px]">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">Edit Field</h2>
                        <button class="text-lg close-edit-modal">✖</button>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=converso&tab=dynamic-fields')); ?>" class="!mt-4">
                        <?php wp_nonce_field('converso_dynamic_fields'); ?>
                        <input type="hidden" name="converso_action" value="edit_field" />
                        <input type="hidden" name="field_id" id="edit--field_id">

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Field Name<span class="text-red-500">*</span></label>
                            <input type="text" id="edit--name" name="edit--name" class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none" required />
                        </div>

                         <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Callable<span class="text-red-500">*</span></label>
                            <input type="text" id="edit--callable" name="edit--callable" class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none" required />
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Value / Placeholder<span class="text-red-500">*</span></label>
                            <input type="text" id="edit--value" name="edit--value" class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none" required />
                        </div>

                        <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all">Save Field</button>
                    </form>
                </div>
            </div>

            <!-- Delete Modal -->
            <div id="delete-field-modal-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
                <div id="delete-field-modal" class="bg-white opacity-0 scale-90 !px-6 !py-6 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out rounded-lg shadow-xl w-[400px]">
                    <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">Delete Field</h2>
                    <p class="text-sm text-gray-600 mt-2">Are you sure you want to delete this field? This action cannot be undone.</p>
                    <form method="post" class="mt-6 flex items-center justify-end gap-3">
                        <?php wp_nonce_field('converso_delete_field'); ?>
                        <input type="hidden" name="converso_action" value="delete_field">
                        <input type="hidden" name="field_id" id="delete-field-id">
                        <button type="button" class="py-2 px-5 rounded bg-gray-200 text-gray-700 close-delete-modal">Cancel</button>
                        <button type="submit" class="py-2 px-5 font-primary text-white rounded cursor-pointer !bg-red-600">Delete</button>
                    </form>
                </div>
            </div>

            <table class="w-full table-fixed text-left mt-5" id="converso-fields-table">
                <thead>
                    <tr>
                        <th class="!text-gray-800 w-[30%] !pb-4 !font-semibold !font-primary">Name</th>
                        <th class="!text-gray-800 w-[20%] !pb-4 !font-semibold !font-primary">Callable</th>
                        <th class="!text-gray-800 w-[30%] !pb-4 !font-semibold !font-primary">Value</th>
                        <th class="!text-gray-800 w-[20%] !pb-4 !font-semibold !font-primary">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($fields)) : ?>
                        <?php foreach ($fields as $field) : ?>
                            <tr class="!mt-4 border-b border-gray-100" id="<?php echo $field['id']; ?>">
                                <td class="py-1">
                                    <h3 class="!text-sm !text-gray-800 !font-normal !font-primary field-name"><?php echo esc_html($field['name']); ?></h3>
                                </td>
                                 <td class="py-1">
                                    <span class="bg-blue-50 rounded px-2 py-1 text-xs font-mono text-blue-600 field-callable"><?php echo esc_html($field['callable']); ?></span>
                                </td>
                                <td class="py-1">
                                    <span class="bg-gray-100 rounded px-2 py-1 text-xs font-mono text-gray-600 field-value"><?php echo esc_html($field['value']); ?></span>
                                </td>
                                <td class="py-1">
                                    <button type="button" class="button bg-button-danger edit-field"><i class="ri-pencil-line"></i></button>
                                    <button type="button" class="button bg-button-danger remove-field"><i class="ri-delete-bin-7-line"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-gray-500">No dynamic fields found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex justify-end gap-2">
                    <div class="flex gap-2">
                        <?php for ($i = 1; $i <= $total_pages; $i++): 
                                $url = add_query_arg(['paged' => $i, 's' => $search, 'sort' => $filters['sort']]);
                        ?>
                            <a href="<?php echo esc_url($url); ?>" class="!px-4 !py-2 !font-primary rounded <?php echo $i === $page ? 'bg-primary !text-white' : 'bg-gray-200'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}