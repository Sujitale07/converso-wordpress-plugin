document.addEventListener("DOMContentLoaded", () => {
    const converso_wp_button = document.querySelector('#converso-wp-button');

    converso_wp_button?.addEventListener("click", async () => {
        try {
            const data = await getLink();
            if (data.success) {
                const agent = data.data;
                console.log("Selected Agent:", agent);

                // Example: Open WhatsApp link in new tab
                if (agent.wa_link) {
                    window.open(agent.wa_link, "_blank");
                }
            } else {
                console.error("Error:", data.data || "Unknown error");
            }
        } catch (err) {
            console.error("Geolocation or fetch error:", err);
        }
    });

    const getLink = () => {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                return reject("Geolocation is not supported by your browser.");
            }

            navigator.geolocation.getCurrentPosition(async (pos) => {
                try {
                    const formData = new FormData();
                    formData.append("action", "converso_reverse_geo");
                    formData.append("lat", pos.coords.latitude);
                    formData.append("lon", pos.coords.longitude);

                    const res = await fetch(converso_ajax.ajax_url, {
                        method: "POST",
                        body: formData
                    });

                    const data = await res.json();
                    resolve(data);
                } catch (err) {
                    reject(err);
                }
            }, err => reject(err.message));
        });
    };
});
