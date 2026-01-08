<?php
namespace Converso\Modules;

use Converso\Core\Log\Log;

class Agents {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        
        add_action('admin_init', [$this, 'handle_add_agent']);
        add_action('admin_init', [$this, 'handle_delete_agent']);
        add_action('admin_init', [$this, 'handle_edit_agent']);

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

    public function handle_add_agent() {

        if (
            !isset($_POST['converso_action']) ||
            $_POST['converso_action'] !== 'add_agent'
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized request', 'converso'));
        }

        check_admin_referer('converso_agents');

        $agents = get_option('converso_agents_data', []);
        if (!is_array($agents)) {
            $agents = [];
        }
    
        $errors = [];

        // Name (required)
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        if (empty($name)) {
            $errors[] = 'name_required';
        }

        // Phone (required)
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        if (empty($phone)) {
            $errors[] = 'phone_required';
        }

        // Greetings (optional)
        $greetings = sanitize_text_field($_POST['greeting'] ?? '');

        // Location (required)
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        if (empty($location)) {
            $errors[] = 'location_required';
        }

        $photo = esc_url_raw($_POST['photo'] ?? '');
        if (!empty($photo) && !filter_var($photo, FILTER_VALIDATE_URL)) {
            $errors[] = 'photo_invalid';
        }

        $is_default = isset($_POST['is_default']) && $_POST['is_default'] === '1';

        $is_offline = isset($_POST['status']) && $_POST['status'] === '1';

        if (!empty($errors)) {
            wp_redirect(
                add_query_arg(
                    ['agent_error' => implode(',', $errors)],
                    admin_url('admin.php?page=converso&tab=agents')
                )
            );
            exit;
        }
    
        if ($is_default) {
            foreach ($agents as &$agent) {
                $agent['default'] = false;
            }
            unset($agent);
        } 

        $agents[] = [
            'id'        => wp_generate_uuid4(),
            'name'      => $name,
            'phone'     => $phone,
            'greetings' => $greetings,
            'location'  => $location,
            'photo'     => $photo,
            'default'   => $is_default,
            'converso_agents_is_offline' => !$is_offline,
            'created_at' => current_time('mysql'),
        ];       

        if (!array_filter($agents, fn($a) => !empty($a['default']))) {
            $agents[0]['default'] = true;
        }
        
        update_option('converso_agents_data', $agents);

        wp_redirect(
            add_query_arg(
                'agent_added',
                1,
                admin_url('admin.php?page=converso&tab=agents')
            )
        );

        exit;
    }

    public function handle_edit_agent() {

        if (
            !isset($_POST['converso_action']) ||
            $_POST['converso_action'] !== 'edit_agent'
        ) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized request', 'converso'));
        }

        // Nonce check
        check_admin_referer('converso_agents');

        // Get agents
        $agents = get_option('converso_agents_data', []);
        if (!is_array($agents)) {
            $agents = [];
        }

        $errors = [];

    
        $agent_id = sanitize_text_field($_POST['agent_id'] ?? '');
        if (empty($agent_id)) {
            $errors[] = 'invalid_agent';
        }

        $name = sanitize_text_field($_POST['edit--name'] ?? '');
        if (empty($name)) {
            $errors[] = 'name_required';
        }

        $phone = sanitize_text_field($_POST['edit--phone'] ?? '');
        if (empty($phone)) {
            $errors[] = 'phone_required';
        }

        $greetings = sanitize_text_field($_POST['edit--greeting'] ?? '');

        $location = sanitize_text_field($_POST['edit--location'] ?? '');
        if (empty($location)) {
            $errors[] = 'location_required';
        }

        // Photo (optional)
        $new_photo = esc_url_raw($_POST['photo'] ?? '');
        if (!empty($new_photo) && !filter_var($new_photo, FILTER_VALIDATE_URL)) {
            $errors[] = 'photo_invalid';
        }

        // Default agent
        $is_default = isset($_POST['edit--is_default']) && $_POST['edit--is_default'] === '1';

        // Status (online/offline)
        $is_online = isset($_POST['edit--status']) && $_POST['edit--status'] === '1';

        /* =========================
        Redirect if errors
        ========================== */
        if (!empty($errors)) {
            wp_redirect(
                add_query_arg(
                    ['agent_error' => implode(',', $errors)],
                    admin_url('admin.php?page=converso&tab=agents')
                )
            );
            exit;
        }

        /* =========================
        Handle Default Agent
        ========================== */
        if ($is_default) {
            foreach ($agents as &$agent) {
                $agent['default'] = false;
            }
            unset($agent);
        }

        /* =========================
        Update Agent
        ========================== */
        $found = false;

        foreach ($agents as &$agent) {
            if ($agent['id'] === $agent_id) {

                $agent['name']      = $name;
                $agent['phone']     = $phone;
                $agent['greetings'] = $greetings;
                $agent['location']  = $location;

                // ✅ PHOTO PRESERVATION LOGIC
                // Only update photo if a new one is provided
                if (!empty($new_photo)) {
                    $agent['photo'] = $new_photo;
                }

                $agent['default'] = $is_default;
                $agent['converso_agents_is_offline'] = !$is_online;
                $agent['updated_at'] = current_time('mysql');

                $found = true;
                break;
            }
        }
        unset($agent);

        /* =========================
        Agent Not Found
        ========================== */
        if (!$found) {
            wp_redirect(
                add_query_arg(
                    ['agent_error' => 'agent_not_found'],
                    admin_url('admin.php?page=converso&tab=agents')
                )
            );
            exit;
        }

        /* =========================
        Ensure One Default Agent
        ========================== */
        if (!array_filter($agents, fn($a) => !empty($a['default']))) {
            $agents[0]['default'] = true;
        }

        /* =========================
        Save & Redirect
        ========================== */
        update_option('converso_agents_data', $agents);

        wp_redirect(
            add_query_arg(
                'agent_updated',
                1,
                admin_url('admin.php?page=converso&tab=agents')
            )
        );
        exit;
    }

    public function handle_delete_agent() {

        if (
            !isset($_POST['converso_action']) ||
            $_POST['converso_action'] !== 'delete_agent'
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized request', 'converso'));
        }

        check_admin_referer('converso_delete_agent');

        $agent_id = sanitize_text_field($_POST['agent_id'] ?? '');

        if (empty($agent_id)) {
            wp_redirect(add_query_arg('agent_deleted', 0));
            exit;
        }

        $agents = get_option('converso_agents_data', []);
        if (!is_array($agents)) {
            $agents = [];
        }

        $agents = array_values(array_filter($agents, function ($agent) use ($agent_id) {
            return $agent['id'] !== $agent_id;
        }));

        // Ensure one default agent exists
        if (!array_filter($agents, fn($a) => !empty($a['default'])) && !empty($agents)) {
            $agents[0]['default'] = true;
        }

        update_option('converso_agents_data', $agents);

        wp_redirect(
            add_query_arg(
                'agent_deleted',
                1,
                admin_url('admin.php?page=converso&tab=agents')
            )
        );
        exit;
    }

    public function get_agents() {

        $agents = get_option('converso_agents_data', []);
        if (!is_array($agents)) {
            $agents = [];
        }

        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $sort   = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';

        $page  = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 5;

        if ($search) {
            $agents = array_filter($agents, function ($agent) use ($search) {
                return
                    stripos($agent['name'] ?? '', $search) !== false ||
                    stripos($agent['phone'] ?? '', $search) !== false;
            });
        }

        if ($status) {
            $agents = array_filter($agents, function ($agent) use ($status) {
                return ($agent['status'] ?? '') === $status;
            });
        }

        if ($sort === 'name') {
            usort($agents, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        }

        if ($sort === 'date') {
            usort($agents, fn($a, $b) => strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? ''));
        }
    
        $total_agents = count($agents);
        $total_pages  = (int) ceil($total_agents / $limit);
        $offset       = ($page - 1) * $limit;

        $agents = array_slice($agents, $offset, $limit);

        return [
            "agents"       => array_values($agents),
            "total_pages"  => $total_pages,
            "total_agents" => $total_agents,
            "page"         => $page,
            "limit"        => $limit,
            "search"       => $search,
            "status"       => $status,
            "sort"         => $sort
        ];
    }


    public function render() {
        $data = $this->get_agents();
        $agents = is_array($data['agents']) ? $data['agents'] : [] ;
    
        $total_pages = $data['total_pages'];
        $limit = $data['limit'];
        $search = $data['search'];
        $page = $data['page'];
        ?>
<div class="wrap relative">
    <div class="!mt-5 flex justify-between items-center">
        <h3 class="font-primary !mb-0 !mt-0 !text-xl">Agents Settings</h3>
        
        <div>
            <button onclick="openModal()" class="bg-primary py-2 px-5 font-primary text-white rounded cursor-pointer"
                id="add-agent"><i class="ri-add-large-line mr-2"></i> Add Agent</button>
        </div>
    </div>
    <div class="flex justify-between items-end gap-5 mt-5">
        

        <div class="w-3/5">
            <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Search</label>
            <div class="relative !mt-2">
                <div class="absolute top-[53%] left-4 -translate-y-1/2">
                    <i class="ri-search-line"></i>
                </div>
                <input type="text" id="agent-search"  placeholder="Search by name or phone"
                    class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-8 !py-3 !bg-gray-200 !border-none">
            </div>
        </div>
        <div class="w-1/5">
            <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Status</label>
            <div class="relative !mt-2">
                <div class="absolute top-[53%] left-4 -translate-y-1/2">
                    <i class="ri-equalizer-2-line"></i>
                </div>
                <select name=""
                    class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-10 !py-3 !bg-gray-200 !border-none"
                    id="agent-status">
                    <option value="">All Status</option>
                    <option value="">Online</option>
                    <option value="">Offline</option>
                </select>
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
                    id="agent-sort">
                    <option value="">All</option>
                    <option value="">Date Created</option>
                    <option value="">Name</option>
                    <option value="">Location</option>
                </select>
            </div>
        </div>    
        <div class="w-1/5">        
            <button class="bg-gray-800 w-full py-2 px-5 !font-primary text-white rounded cursor-pointer" id="apply-filter"> Apply Filter</button>
        </div>
    </div>
    
    <div class="toast-placeholder">
        <?php settings_errors("converso_agents_settings") ?>
    </div>

    <div id="modalBackdrop"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="addAgentModal"
            class="add-agent-modal opacity-0 scale-90 !px-6 !py-5 !h-[80%]  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out overflow-y-scroll">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">
                    Add New Agent
                </h2>
                <button onclick="closeModal()" class="text-lg">✖</button>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=converso&tab=agents')); ?>"
                class="!mt-4" id="agentForm">
                <?php wp_nonce_field('converso_agents'); ?>
                <input type="hidden" name="converso_action" value="add_agent" />
                <div class="mb-8 agent-photo-picker">
                    <div class="flex items-center gap-6">
                        <div
                            class="photo-preview w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-dashed border-blue-300 flex items-center justify-center overflow-hidden">
                            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>

                        <div class="flex-1 mt-2">
                            <button type="button"
                                class="choose-photo-btn px-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg font-medium border border-blue-200">
                                Choose Photo
                            </button>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>

                    <input type="hidden" name="photo" class="photo-input">
                </div>

                <!-- Name and Phone -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name<span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="Eg. Sujit Ale Magar"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span
                                class="text-red-500">*</span></label>
                        <input type="text" name="phone" placeholder="Eg. +977 9813213123"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>
                </div>

                <!-- Location and Default -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location<span
                                class="text-red-500">*</span></label>
                        <input type="text" name="location" placeholder="Eg. Kathmandu, Nepal"
                            class="location-select w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set as Default<span
                                class="text-red-500">*</span></label>
                        <select name="is_default"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class=" mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Greeting <span
                            class="text-red-500">*</span></label>
                    <textarea name="greeting" placeholder="Eg. Hii How Can I Help You" rows="3"
                        class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                        required></textarea>
                </div>

                <!-- Status Toggle -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agent Status</label>
                            <p class="text-xs text-gray-500 mt-1">Enable or disable this agent</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="1" class="sr-only peer" checked>
                            <div
                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all">
                    Save Agent
                </button>
            </form>
        </div>
    </div>

    <div id="delete-agent-modal-backdrop"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="delete-agent-modal"
            class="bg-white opacity-0 scale-90 !px-6 !py-6  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out">

            <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">
                Delete Agent
            </h2>

            <p class="text-sm text-gray-600 mt-2">
                Are you sure you want to delete this agent? This action cannot be undone.
            </p>

            <form method="post" class="mt-6 flex items-center justify-end gap-3">
                <?php wp_nonce_field('converso_delete_agent'); ?>

                <input type="hidden" name="converso_action" value="delete_agent">
                <input type="hidden" name="agent_id" id="delete-agent-id">

                <button type="button" class="py-2 px-5  rounded bg-gray-200 text-gray-700 close-delete-modal">
                    Cancel
                </button>

                <button type="submit" class="py-2 px-5 font-primary text-white rounded cursor-pointer !bg-red-600">
                    Delete
                </button>
            </form>

        </div>
    </div>

    <div id="edit-agent-modal-backdrop"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="edit-agent-modal"
            class="add-agent-modal opacity-0 scale-90 !px-6 !py-5  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out overflow-y-scroll max-h-[50%]">

            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">
                    Edit Agent
                </h2>
                <button class="text-lg close-edit-modal">✖</button>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=converso&tab=agents')); ?>"
                class="!mt-4" id="agentForm">
                <?php wp_nonce_field('converso_agents'); ?>
                <input type="hidden" name="converso_action" value="edit_agent" />

                <div class="mb-8 agent-photo-picker">
                    <div class="flex items-center gap-6">
                        <div id="edit--photoPreview"
                            class="photo-preview w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-dashed border-blue-300 flex items-center justify-center overflow-hidden">
                            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>

                        <div class="flex-1 mt-2">
                            <button type="button"
                                class="choose-photo-btn px-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg font-medium border border-blue-200">
                                Choose Photo
                            </button>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>

                    <input type="hidden" name="photo" class="photo-input">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit--name" name="edit--name" placeholder="Eg. Sujit Ale Magar"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit--phone" name="edit--phone" placeholder="Eg. +977 9813213123"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit--location" name="edit--location" placeholder="Eg. Kathmandu, Nepal"
                            class="location-select w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set as Default<span
                                class="text-red-500">*</span></label>
                        <select id="edit--is_default" name="edit--is_default"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class=" mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Greeting <span
                            class="text-red-500">*</span></label>
                    <textarea id="edit--greeting" name="edit--greeting" placeholder="Eg. Hii How Can I Help You"
                        rows="3"
                        class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                        required></textarea>
                </div>

                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agent Status</label>
                            <p class="text-xs text-gray-500 mt-1">Enable or disable this agent</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="edit--status" name="edit--status" value="1" class="sr-only peer"
                                checked>
                            <div
                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>
                <input type="hidden" name="agent_id" id="edit--agent_id">

                <button type="submit"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all">Save
                    Agent</button>
            </form>
        </div>
    </div>

    <div>
        <table class="w-full table-fixed text-left  mt-5" id="converso-agents-table">
            <thead>
                <tr>
                    <th class="!text-gray-800 w-[8%]  !pb-4 !font-semibold !font-primary">Photo</th>
                    <th class="!text-gray-800 w-[25%] !pb-4 !font-semibold !font-primary">Name</th>
                    <th class="!text-gray-800 w-[16%] !pb-4 !font-semibold !font-primary">Phone</th>
                    <th class="!text-gray-800 w-[23%] !pb-4 !font-semibold !font-primary">Location</th>
                    <th class="!text-gray-800 w-[12%] !pb-4 !font-semibold !font-primary">Status</th>
                    <th class="!text-gray-800 w-[16%] !pb-4 !font-semibold !font-primary">Actions</th>

                </tr>
            </thead>
            <tbody>
                <?php if (!empty($agents)) : ?>

                <?php foreach ($agents as $index => $agent) : ?>
                <tr class="!mt-4" id="<?php echo $agent['id']?>">
                    <td class="hidden agent-greeting">
                        <?php echo esc_attr($agent['greetings']); ?>
                    </td>
                    <td>
                        <img src="<?php echo esc_attr($agent['photo'] ?? ''); ?>"
                            class="agent-image rounded-full w-full aspect-square max-w-7 object-cover" alt="">
                    </td>
                    <td class="">
                        <h3 class="!text-sm !text-gray-800 !font-normal !font-primary agent-name">
                            <?php echo esc_attr($agent['name']); ?>   <?php echo !empty($agent['default']) 
                                ? "<span class='bg-cyan-600 ml-2 rounded-xl !text-xs !font-primary px-4 py-1 text-gray-100'>Default</span>" 
                                : ""; 
                            ?>
                        </h3>
                    </td>
                    <td class="">
                        <h3 class="!text-sm !text-gray-800 !font-normal !font-primary agent-phone">
                            <?php echo esc_attr($agent['phone']); ?>
                        </h3>
                    </td>
                    <td>
                        <span class="bg-gray-300 rounded-xl !text-xs !font-primary px-4 py-1 text-black agent-location"><?php echo esc_attr($agent['location']); ?></span>
                    </td>
                    <td class="">
                        <h3 class="!text-sm !text-gray-800 !font-normal !font-primary agent-status">
                            <?php echo !empty($agent['converso_agents_is_offline']) ? "<span class='text-gray-700'>Offline</span>" : "<span class='text-green-700'>Online</span>" ?>
                        </h3>
                    </td>                
                    <td>
                        <button type="button" class="button bg-button-danger edit-agent"><i
                                class="ri-pencil-line"></i></button>
                        <button type="button" class="button bg-button-danger remove-agent"><i
                                class="ri-delete-bin-7-line"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-end gap-2">
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                                        $url = add_query_arg([
                                            'paged' => $i,
                                            's'     => $search,
                                            'limit' => $limit,
                                        ]);
                                    ?>
                    <a href="<?php echo esc_url($url); ?>"
                        class="!px-4 !py-2 !font-primary rounded <?php echo $i === $page ? 'bg-primary !text-white' : 'bg-gray-200'; ?>">
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