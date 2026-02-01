document.addEventListener("DOMContentLoaded", () => {
    const connectapre_wp_button = document.querySelector('#connectapre-wp-button');

    const getVisitorId = () => {
        let visitorId = localStorage.getItem('connectapre_visitor_id');
        if (!visitorId) {
            visitorId = 'cv_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
            localStorage.setItem('connectapre_visitor_id', visitorId);
        }
        return visitorId;
    };

    let visitorLocation = null;

    const registerClick = async (agentId) => {
        try {
            const formData = new FormData();
            formData.append("action", "connectapre_register_click");
            formData.append("nonce", connectapre_ajax.nonce);
            formData.append("agent_id", agentId);
            formData.append("visitor_id", getVisitorId());
            formData.append("page_path", window.location.pathname);
            
            if (visitorLocation) {
                formData.append("location_city", visitorLocation.location_city || "");
                formData.append("location_state", visitorLocation.location_state || "");
                formData.append("location_country", visitorLocation.location_country || "");
            }

            await fetch(connectapre_ajax.ajax_url, {
                method: "POST",
                body: formData
            });
        } catch (err) {
            console.error("Click registration error:", err);
        }
    };

    connectapre_wp_button?.addEventListener("click", async () => {
        try {
            const data = await getLink();
            
            if (!data || typeof data !== 'object') {
                throw new Error("Invalid response from server");
            }

            if (data.success && data.data) {
                const response = data.data; 
                const agent = response.agent || null;
                const location = response.visitor_location || { country: 'Unknown', state: '', city: 'Unknown' };

                // Capture detected location regardless of agent match
                visitorLocation = {
                    location_city: location.city || "Unknown",
                    location_state: location.state || "",
                    location_country: location.country || "Unknown"
                };

                if (agent && agent.wa_link) {
                    console.log("Selected Agent:", agent.name || "Unknown");
                    window.open(agent.wa_link, "_blank");
                    registerClick(agent.id || 0);
                } else {
                    console.warn("No agent routed for location:", visitorLocation);
                    registerClick(0); // Register attempt with no agent
                }
            } else {
                console.error("Connectapre Error:", data.data || "Unknown server error");
            }
        } catch (err) {
            console.error("Connectapre Connection Failure:", err.message);
            // Even on failure, we want the button to feel responsive
            // If we have a fallback URL or something, we could use it here.
        }
    });

    const getLink = () => {
        return new Promise((resolve) => {
            if (!navigator.geolocation) {
                console.warn("Geolocation not supported.");
                return resolve({ success: true, data: { agent: null, visitor_location: { country: "Unknown", state: "", city: "Unknown" } } });
            }

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    try {
                        const formData = new FormData();
                        formData.append("action", "connectapre_reverse_geo");
                        formData.append("nonce", connectapre_ajax.nonce);
                        formData.append("lat", pos.coords.latitude);
                        formData.append("lon", pos.coords.longitude);

                        const res = await fetch(connectapre_ajax.ajax_url, {
                            method: "POST",
                            body: formData
                        });

                        const data = await res.json();
                        resolve(data);
                    } catch (err) {
                        console.error("Reverse geo error:", err);
                        resolve({ success: true, data: { agent: null, visitor_location: { country: "Unknown", state: "", city: "Unknown" } } });
                    }
                },
                (err) => {
                    console.warn("Geolocation denied or failed:", err.message);
                    // Fallback: Proceed with empty location to trigger default agent routing
                    fetch(connectapre_ajax.ajax_url, {
                        method: "POST",
                        body: new URLSearchParams({ action: "connectapre_reverse_geo", nonce: connectapre_ajax.nonce, lat: "", lon: "" })
                    })
                    .then(res => res.json())
                    .then(data => resolve(data))
                    .catch(() => {
                        resolve({ success: true, data: { agent: null, visitor_location: { country: "Unknown", state: "", city: "Unknown" } } });
                    });
                },
                { timeout: 5000 }
            );
        });
    };

    // --- Delay Logic ---
    const displayDelay = parseInt(connectapre_ajax.display_delay) || 0;
    const scrollDelay = parseInt(connectapre_ajax.scroll_delay) || 0;

    const showButton = () => {
        if (connectapre_wp_button) {
            connectapre_wp_button.style.display = 'block';
            connectapre_wp_button.style.opacity = '1';
        }
    };

    if (connectapre_wp_button) {
        if (displayDelay > 0 || scrollDelay > 0) {
            connectapre_wp_button.style.display = 'none';
            connectapre_wp_button.style.opacity = '0';
            connectapre_wp_button.style.transition = 'opacity 0.5s ease';

            let timerTriggered = false;
            let scrollTriggered = false;

            const checkAndShow = () => {
                // If both are set, show when either condition is met? 
                // Or both? Usually it's "show after X seconds OR after Y scroll".
                // We'll go with "either" as it's the more common behavior.
                if (timerTriggered || scrollTriggered) {
                    showButton();
                }
            };

            if (displayDelay > 0) {
                setTimeout(() => {
                    timerTriggered = true;
                    checkAndShow();
                }, displayDelay * 1000);
            }

            if (scrollDelay > 0) {
                const handleScroll = () => {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    const scrolled = (scrollTop / scrollHeight) * 100;

                    if (scrolled >= scrollDelay) {
                        scrollTriggered = true;
                        window.removeEventListener('scroll', handleScroll);
                        checkAndShow();
                    }
                };
                window.addEventListener('scroll', handleScroll);
            }
        } else {
            showButton();
        }
    }
});


