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

        <div class="wrap relative !mt-8">
            <div class="grid grid-cols-12 gap-4 ">
                <div class="h-full col-span-7 font-primary bg-white  rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Overview</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Current Routing Status at a glance</p>

                    <div class="grid grid-cols-3 p-3">
                        <div>
                            <h4 class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Total Agents</h4>
                            <p class="!my-3 !font-semibold font-secondary !text-2xl !text-black">4</p>
                            <p class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">People who can receive chats</p>
                        </div>
                        <div>
                            <h4 class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Agents online now</h4>
                            <p class="!my-3 !font-semibold font-secondary !text-2xl !text-black">2</p>                            
                            <p class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Available to answer visitors</p>
                        </div>
                        <div>
                            <h4 class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Chats today</h4>
                            <p class="!my-3 !font-semibold font-secondary !text-2xl !text-black">18</p>                            
                            <p class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Messages started from the widget</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="!text-sm !text-black border-b !mb-2 !border-gray-200 !pb-4 !mt-4">Quick links</h4>
                        <div class="flex flex-col divide-y gap-3 divide-gray-200">
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Manage agents</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Add, edit or pause WhatsApp recipients</p>
                                </div>
                                <div>
                                    <a href="" class="bg-green-600 !text-white !font-primary py-2 px-5 font-normal rounded cursor-pointer">Go to Agents</a>
                                </div>
                            </div>
                           
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Visibility</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Enabled on all pages Shown on desktop & mobile</p>
                                </div>
                                <div>
                                    <a href="" class="bg-gray-300 !text-black !font-primary py-2 px-5 font-primary rounded cursor-pointer">Open Dynamic Fields</a>
                                </div>
                            </div>
                           
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Widget Design</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Adjust button style, colors, and position</p>
                                </div>
                                <div>
                                       <a href="" class="bg-gray-300 !text-black !font-primary py-2 px-5 font-primary rounded cursor-pointer">Styling & Position</a>
                                </div>
                            </div>
                           

                        </div>
                    </div>
                    
                </div>
                <div class="h-full col-span-5 bg-white  rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Widget Status</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Where and how the widget is live</p>

                    <div>
                        
                        <div class="flex flex-col divide-y gap-3 divide-gray-200">
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Visibility</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Enabled on all pages Shwon on desktop & mobile</p>
                                </div>
                                <div>
                                    <a href="" class="bg-gray-200 !text-gray-500 !font-primary p-2 font-normal rounded !text-xs cursor-pointer">Active</a>
                                </div>
                            </div>                                                                              
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Most Active Page</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">/pricing - 14 chats today</p>
                                </div>
                               
                            </div>                           
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Last test chat</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">2 minutes ago Opened Successfully</p>
                                </div>
                                <div>
                                    <a href="" class="bg-gray-300 !text-black !font-primary py-2 px-5 font-primary rounded cursor-pointer">Run test</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 !mt-4">
                <div class="h-full col-span-5 bg-white  rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Agents Activity</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Who is currently receiving chats</p>
                </div>
                <div class="h-full col-span-7 font-primary bg-white  rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Integration & Test</h3>
                    <p class="!mt-2 !text-sm text-gray-500">How Converso behaves on your site</p>
                </div>
            </div>
        </div>


        <?php
    }
}
