(function () {
    "use strict";

    const stateNode = document.getElementById("dashboardState");
    const dashboardState = stateNode ? JSON.parse(stateNode.textContent || "{}") : {};
    const offlineBanner = document.getElementById("offlineBanner");
    const onlineStateText = document.getElementById("onlineStateText");
    const queueCountText = document.getElementById("queueCountText");
    const queuedOperations = document.getElementById("queuedOperations");
    const payloadPreview = document.getElementById("reportPayloadPreview");
    const reportButton = document.getElementById("requestReportBtn");

    function saveDashboardState() {
        const payload = {
            ...dashboardState,
            savedAt: new Date().toISOString(),
            online: navigator.onLine
        };
        localStorage.setItem("agritwin:last-dashboard-state", JSON.stringify(payload));
        updateSyncText(payload);
    }

    function restoreDashboardState() {
        const raw = localStorage.getItem("agritwin:last-dashboard-state");
        if (!raw || navigator.onLine) {
            return;
        }
        try {
            const cached = JSON.parse(raw);
            updateSyncText(cached);
        } catch (error) {
            console.warn("Could not restore AgriTwin dashboard cache", error);
        }
    }

    function updateOnlineState() {
        const online = navigator.onLine;
        if (offlineBanner) {
            offlineBanner.hidden = online;
        }
        if (onlineStateText) {
            onlineStateText.textContent = online ? "Online" : "Offline";
        }
        document.body.dataset.connectivity = online ? "online" : "offline";
    }

    function updateSyncText(state) {
        const lastSyncText = document.getElementById("lastSyncText");
        if (lastSyncText && state.savedAt) {
            lastSyncText.textContent = "Saved offline: " + new Date(state.savedAt).toLocaleString();
        }
    }

    function reportPayload() {
        return {
            "@context": ["https://w3id.org/ocsm/main-context.jsonld"],
            "@type": "AgriTwinAdvisoryReportRequest",
            "title": "AgriTwin advisory report",
            "createdAt": new Date().toISOString(),
            "farm": dashboardState.farm || {},
            "lastSyncTime": dashboardState.lastSyncTime || null,
            "queuedOperations": Number(dashboardState.queuedOperations || 0),
            "cards": dashboardState.cards || {},
            "serviceStatuses": dashboardState.serviceStatuses || {},
            "requestedReports": ["irrigation", "fertilization", "pest", "advisory"],
            "privacy": {
                "rawPilotDataIncluded": false,
                "parcelBoundariesIncluded": false
            }
        };
    }

    function queueReportRequest(payload) {
        const queue = JSON.parse(localStorage.getItem("agritwin:queued-operations") || "[]");
        queue.push({
            id: "report-" + Date.now(),
            type: "report_request",
            createdAt: new Date().toISOString(),
            payload
        });
        localStorage.setItem("agritwin:queued-operations", JSON.stringify(queue));
        if (queueCountText) {
            queueCountText.textContent = "Queue: " + queue.length;
        }
        if (queuedOperations) {
            queuedOperations.textContent = String(queue.length);
        }
    }

    function downloadJson(payload) {
        const blob = new Blob([JSON.stringify(payload, null, 2)], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = "agritwin-advisory-payload.json";
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    }

    function prepareReport() {
        const payload = reportPayload();
        if (payloadPreview) {
            payloadPreview.textContent = JSON.stringify(payload, null, 2);
        }
        queueReportRequest(payload);
        saveDashboardState();
        fetch("report_advisory.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
            .then((response) => response.json())
            .then((result) => {
                if (payloadPreview) {
                    payloadPreview.textContent = JSON.stringify({ request: payload, backend: result }, null, 2);
                }
                downloadJson(payload);
            })
            .catch(() => {
                downloadJson(payload);
            })
            .finally(() => {
                window.print();
            });
    }

    function activateCurrentSection() {
        const links = Array.from(document.querySelectorAll(".side-nav a[href^='#']"));
        const sections = links
            .map((link) => document.querySelector(link.getAttribute("href")))
            .filter(Boolean);

        let active = sections[0];
        for (const section of sections) {
            if (section.getBoundingClientRect().top <= 120) {
                active = section;
            }
        }

        links.forEach((link) => {
            link.classList.toggle("active", active && link.getAttribute("href") === "#" + active.id);
        });
    }

    window.addEventListener("online", updateOnlineState);
    window.addEventListener("offline", updateOnlineState);
    window.addEventListener("scroll", activateCurrentSection, { passive: true });

    document.getElementById("refreshStateBtn")?.addEventListener("click", saveDashboardState);
    document.getElementById("clearOfflineStateBtn")?.addEventListener("click", function () {
        localStorage.removeItem("agritwin:last-dashboard-state");
        localStorage.removeItem("agritwin:queued-operations");
        if (queueCountText) {
            queueCountText.textContent = "Queue: 0";
        }
        if (queuedOperations) {
            queuedOperations.textContent = "0";
        }
    });
    reportButton?.addEventListener("click", prepareReport);

    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js").catch((error) => {
            console.warn("AgriTwin service worker registration failed", error);
        });
    }

    updateOnlineState();
    restoreDashboardState();
    activateCurrentSection();
    saveDashboardState();
    if (payloadPreview) {
        payloadPreview.textContent = JSON.stringify(reportPayload(), null, 2);
    }
})();
