<?php
namespace Converso\Modules;
use \Converso\Services\ClicksService;
use \Converso\Services\AgentsService;
class General {

    public function __construct() {
        // Dashboard logic if needed
    }

    public function render() {
        $business_name = get_option('converso_business_name', '');
        $business_type = get_option('converso_business_type', '');
        $cta_text = get_option('converso_cta_text', '');
        $display_delay = get_option('converso_display_delay', '');
        $scroll_delay = get_option('converso_scroll_delay', '');
        $enable_whatsapp = get_option('converso_enable_whatsapp', 0);
        $converso_hide_when_offline = get_option('converso_hide_when_offline', '');
        
        $agents_url =  add_query_arg(
            [],
            admin_url('admin.php?page=converso&tab=agents')
        );
        $dynamic_fields_url =  add_query_arg(
            [],
            admin_url('admin.php?page=converso&tab=dynamic_fields')
        );
        $design_url = add_query_arg(
            [],
            admin_url('admin.php?page=converso&tab=styling-and-positioning')
        );

        $agents_data = AgentsService::get_agents(['limit' => 1]);
        $total_agents = $agents_data['total_agents'];
        
        $clicks_today = ClicksService::get_total_clicks_today();
        $total_clicks = ClicksService::get_total_clicks();
        
        $most_active_page_data = ClicksService::get_most_active_page();
        $most_active_page = $most_active_page_data ? $most_active_page_data->page_path : 'N/A';
        $most_active_count = $most_active_page_data ? $most_active_page_data->count : 0;

        $agents_activity = ClicksService::get_clicks_by_agent();

        // New Detailed Analytics Data
        $top_pages = ClicksService::get_top_pages(5);
        $top_countries = ClicksService::get_top_locations('country', 5);
        $recent_chats = ClicksService::get_recent_clicks(8);
        ?>

        <div class="wrap relative !mt-5">
            <div class="grid grid-cols-12 gap-4 ">
                <div class="h-full col-span-7 font-primary bg-white  rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Overview</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Current Routing Status at a glance</p>

                    <div class="grid grid-cols-3 p-3">
                        <div>
                            <h4 class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Total Agents</h4>
                            <p class="!my-3 !font-semibold font-secondary !text-2xl !text-black"><?php echo esc_html($total_agents); ?></p>
                            <p class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">People who can receive chats</p>
                        </div>
                        <div>
                            <h4 class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Total Clicks</h4>
                            <p class="!my-3 !font-semibold font-secondary !text-2xl !text-black"><?php echo esc_html($total_clicks); ?></p>                            
                            <p class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Cumulative widget interaction</p>
                        </div>
                        <div>
                            <h4 class="!my-0 !font-normal text-gray-500 font-secondary !text-xs">Chats today</h4>
                            <p class="!my-3 !font-semibold font-secondary !text-2xl !text-black"><?php echo esc_html($clicks_today); ?></p>                            
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
                                    <a href="<?php echo $agents_url ?>" target="_blank" class="bg-green-600 !text-white !font-primary py-2 px-5 font-normal rounded cursor-pointer transition-all hover:bg-green-700">Go to Agents</a>
                                </div>
                            </div>
                           
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Dynamic Fields</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Control placeholders like {site_name} or {promo_code}</p>
                                </div>
                                <div>
                                    <a href="<?php echo $dynamic_fields_url ?>" target="_blank" class="bg-gray-300 !text-black !font-primary py-2 px-5 font-primary rounded cursor-pointer transition-all hover:bg-gray-400">Open Dynamic Fields</a>
                                </div>
                            </div>
                           
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Widget Design</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Adjust button style, colors, and position</p>
                                </div>
                                <div>
                                       <a href="<?php echo $design_url?>" target="_blank" class="bg-gray-300 !text-black !font-primary py-2 px-5 font-primary rounded cursor-pointer transition-all hover:bg-gray-400">Styling & Position</a>
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
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Status: <?php echo $enable_whatsapp ? 'Active' : 'Disabled'; ?></p>
                                </div>
                                <div>
                                    <span class="<?php echo $enable_whatsapp ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> !font-primary p-2 font-normal rounded !text-xs">
                                        <?php echo $enable_whatsapp ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>                                                                              
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Most Active Page</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs"><?php echo esc_html($most_active_page); ?> - <?php echo (int)$most_active_count; ?> chats</p>
                                </div>
                            </div>                           
                            <div class="flex justify-between items-center pb-3">
                                <div>
                                    <h3 class="!text-sm !mt-2 !font-secondary !mb-0">Last stats sync</h3>
                                    <p class="!my-1 !font-normal text-gray-500 font-secondary !text-xs">Just now</p>
                                </div>
                                <div>
                                    <button onclick="window.location.reload()" class="bg-gray-300 !text-black !font-primary py-2 px-5 font-primary rounded cursor-pointer">Refresh</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Analytics Data Rows -->
            <div class="grid grid-cols-12 gap-4 !mt-4">
                <div class="h-full col-span-4 bg-white rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Top Markets</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Visitor distribution by country</p>
                    <div class="!mt-4 space-y-3">
                        <?php if (empty($top_countries)) : ?>
                            <p class="text-xs text-gray-400 italic">No location data captured yet.</p>
                        <?php else : ?>
                            <?php foreach ($top_countries as $loc) : ?>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 truncate max-w-[140px]"><?php echo esc_html($loc->label); ?></span>
                                    <span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-semibold"><?php echo (int)$loc->count; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="h-full col-span-8 bg-white rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Top Engagement Pages</h3>
                    <p class="!mt-2 !text-sm text-gray-500">URLs initiating the most chats</p>
                    <div class="!mt-4 space-y-4">
                        <?php if (empty($top_pages)) : ?>
                            <p class="text-xs text-gray-400 italic">Waiting for page-level interactions...</p>
                        <?php else : ?>
                            <?php foreach ($top_pages as $page) : ?>
                                <div class="space-y-1">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-blue-600 truncate font-mono max-w-[300px]"><?php echo esc_html($page->page_path); ?></span>
                                        <span class="font-bold text-gray-700"><?php echo (int)$page->count; ?></span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-1 rounded-full overflow-hidden">
                                        <div class="bg-blue-500 h-full" style="width: <?php echo ($total_clicks > 0) ? ($page->count / $total_clicks * 100) : 0; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-4 !mt-4">
                <div class="h-full col-span-12 bg-white rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Recent Live Interactions</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Real-time engagement feed</p>
                    <div class="!mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] uppercase text-gray-400 border-b border-gray-100">
                                    <th class="py-2 font-semibold">Time</th>
                                    <th class="py-2 font-semibold">Location</th>
                                    <th class="py-2 font-semibold">Handled By</th>
                                    <th class="py-2 font-semibold">Source Page</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($recent_chats)) : ?>
                                    <tr><td colspan="4" class="py-6 text-xs text-center text-gray-400">Listening for clicks...</td></tr>
                                <?php else : ?>
                                    <?php foreach ($recent_chats as $chat) : ?>
                                        <tr class="text-xs">
                                            <td class="py-3 text-gray-500"><?php echo human_time_diff(strtotime($chat->created_at ?? 'now'), current_time('timestamp')); ?> ago</td>
                                            <td class="py-3 font-medium"><?php echo esc_html(($chat->location_city ?? '') ?: 'Unknown'); ?>, <?php echo esc_html(($chat->location_country ?? '') ?: 'Planet Earth'); ?></td>
                                            <td class="py-3"><span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-[10px]"><?php echo esc_html($chat->agent_name ?? 'System'); ?></span></td>
                                            <td class="py-3 font-mono text-gray-400 text-[10px]"><?php echo esc_html($chat->page_path ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-4 !mt-4">
                <div class="h-full col-span-12 bg-white rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl">Export Reports</h3>
                    <p class="!mt-2 !text-sm text-gray-500">Download detailed CSV reports of your site's chat activity.</p>
                    
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="!mt-6 flex flex-wrap gap-4 items-end">
                        <input type="hidden" name="action" value="converso_export_report">
                        <?php wp_nonce_field('converso_export_report_nonce'); ?>
                        
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-500 !mb-1 !font-secondary">Filter by Agent</label>
                            <select name="agent_id" class="!bg-gray-50  !h-10 !border-gray-200 !text-sm !rounded !py-2 !px-3">
                                <option value="0">All Agents</option>
                                <?php 
                                $all_agents_data = AgentsService::get_agents(['limit' => 999]);
                                foreach ($all_agents_data['agents'] as $agent): ?>
                                    <option value="<?php echo (int)$agent['id']; ?>"><?php echo esc_html($agent['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="!text-xs !font-semibold !text-gray-500 !mb-1 !font-secondary">Start Date</label>
                            <input type="date" name="start_date" class="!bg-gray-50 !h-10 !font-secondary !border-gray-200 !text-sm !rounded !py-2 !px-3">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="!text-xs !font-semibold !text-gray-500 !mb-1 !font-secondary">End Date</label>
                            <input type="date" name="end_date" class="!bg-gray-50 !h-10 !font-secondary !border-gray-200 !text-sm !rounded !py-2 !px-3">
                        </div>

                        <button type="submit" class="bg-gray-800 !font-secondary !h-10 !text-white !font-primary py-2 px-6 rounded font-primary text-sm transition-all hover:bg-black cursor-pointer">
                            Download CSV Report
                        </button>
                    </form>
                </div>
            </div>
        </div>



        <?php
    }
}
