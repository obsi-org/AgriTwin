/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

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
    const translations = window.agritwinI18n || {};
    const t = (key, fallback) => translations[key] || fallback;

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
            console.warn(t("cache_restore_warning", "Could not restore AgriTwin dashboard cache"), error);
        }
    }

    function updateOnlineState() {
        const online = navigator.onLine;
        if (offlineBanner) {
            offlineBanner.hidden = online;
        }
        if (onlineStateText) {
            onlineStateText.textContent = online ? t("online", "Online") : t("offline", "Offline");
        }
        document.body.dataset.connectivity = online ? "online" : "offline";
    }

    function updateSyncText(state) {
        const lastSyncText = document.getElementById("lastSyncText");
        if (lastSyncText && state.savedAt) {
            lastSyncText.textContent = t("saved_offline", "Saved offline") + ": " + new Date(state.savedAt).toLocaleString();
        }
    }

    function reportPayload() {
        return {
            "@context": ["https://w3id.org/ocsm/main-context.jsonld"],
            "@type": "AgriTwinAdvisoryReportRequest",
            "title": t("advisory_report_title", "AgriTwin advisory report"),
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
            queueCountText.textContent = t("queue", "Queue") + ": " + queue.length;
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
        const links = Array.from(document.querySelectorAll(".side-nav a"));
        const pagePath = window.location.pathname.split("/").pop() || "index.php";
        const anchorLinks = links.filter((link) => (link.getAttribute("href") || "").startsWith("#"));
        const sections = anchorLinks
            .map((link) => document.querySelector(link.getAttribute("href")))
            .filter(Boolean);

        if (!sections.length) {
            links.forEach((link) => {
                const href = link.getAttribute("href") || "";
                link.classList.toggle("active", !href.startsWith("#") && href.split("/").pop() === pagePath);
            });
            return;
        }

        let active = sections[0];
        for (const section of sections) {
            if (section.getBoundingClientRect().top <= 120) {
                active = section;
            }
        }

        anchorLinks.forEach((link) => {
            link.classList.toggle("active", active && link.getAttribute("href") === "#" + active.id);
        });
    }

    function initHelpSearch() {
        const config = window.agritwinHelpSearch;
        if (!config) {
            return;
        }
        const input = document.getElementById(config.inputId);
        const noResults = document.getElementById(config.noResultsId);
        const sections = Array.from(document.querySelectorAll("[data-help-section]"));
        if (!input || !sections.length) {
            return;
        }
        input.addEventListener("input", function () {
            const query = input.value.trim().toLocaleLowerCase();
            let visible = 0;
            sections.forEach((section) => {
                const text = (section.dataset.helpText || "").toLocaleLowerCase();
                const match = query === "" || text.includes(query);
                section.hidden = !match;
                if (match) {
                    visible += 1;
                }
            });
            if (noResults) {
                noResults.hidden = visible > 0;
            }
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
            queueCountText.textContent = t("queue", "Queue") + ": 0";
        }
        if (queuedOperations) {
            queuedOperations.textContent = "0";
        }
    });
    reportButton?.addEventListener("click", prepareReport);

    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js").catch((error) => {
            console.warn(t("service_worker_warning", "AgriTwin service worker registration failed"), error);
        });
    }

    updateOnlineState();
    initHelpSearch();
    restoreDashboardState();
    activateCurrentSection();
    saveDashboardState();
    if (payloadPreview) {
        payloadPreview.textContent = JSON.stringify(reportPayload(), null, 2);
    }
})();
