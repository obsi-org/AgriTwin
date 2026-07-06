<?php
/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

return [
    [
        'id' => 'getting-started',
        'title' => 'Getting Started',
        'audience' => 'All users',
        'items' => [
            'AgriTwin supports daily agricultural decisions for Fotovoltaica Macotera SL by combining field telemetry, weather inputs, OpenAgri service status and advisory outputs.',
            'Log in with the username assigned by the administrator. If the account requires a password change, open the Profile page and set a new password before continuing.',
            'Change the interface language from the selector in the header. The selection is kept across pages through the browser session and cookie.',
            'Access levels are role based: Administrator, Technical Manager, Farm Manager, Advisor / Researcher and Viewer. Restricted pages show configuration or access messages when the current role cannot perform an action.',
        ],
    ],
    [
        'id' => 'dashboard',
        'title' => 'Dashboard',
        'audience' => 'Farm Manager, Advisor / Researcher, Viewer',
        'items' => [
            'The Dashboard is the operational control centre for the current farm state, showing water stress, irrigation, pest pressure, nitrogen status, weather and OpenAgri readiness.',
            'Weather cards distinguish external weather service data from on-farm station readings. Check the source label before using weather values for decisions.',
            'Sensor station cards summarise station count, crop block, latest readings and confidence. Reduced confidence means the data should be reviewed before acting.',
            'Core OpenAgri service readiness covers Gatekeeper, Weather, Irrigation and Pest & Disease services. The card indicates how many core services are currently available.',
        ],
    ],
    [
        'id' => 'analytics',
        'title' => 'Analytics',
        'audience' => 'Farm Manager, Technical Manager, Advisor / Researcher',
        'items' => [
            'Use the time-range, station and crop filters to focus charts on the last 24 hours, 7 days or 30 days where data is available.',
            'Weather charts combine temperature, humidity, wind and rainfall so irrigation and pest conditions can be interpreted together.',
            'Sensor charts compare soil moisture and soil temperature by station. Trend arrows and threshold colours identify blocks needing attention.',
            'Irrigation, pest and KPI panels show volume, energy, risk, latency, continuity and freshness indicators for operational review.',
        ],
    ],
    [
        'id' => 'sensor-stations',
        'title' => 'Sensor Stations',
        'audience' => 'Farm Manager, Technical Manager, Viewer',
        'items' => [
            'Stations are named by site and block, for example MAC-ALM-A for Almond Block A. Each station groups multiple readings under one field asset.',
            'Units include soil moisture VWC %, soil temperature degC, air temperature degC, relative humidity %, rainfall mm, wind speed km/h, solar radiation W/m2, irrigation flow m3/h and energy kWh.',
            'Freshness is based on the latest timestamp. Old or incomplete values reduce confidence and should be checked against field conditions.',
            'Common issues include missing power, poor radio coverage, blocked rain gauges, disconnected probes and delayed synchronisation.',
        ],
    ],
    [
        'id' => 'weather-openagri',
        'title' => 'Weather and OpenAgri Weather',
        'audience' => 'Farm Manager, Technical Manager',
        'items' => [
            'On-farm readings come from local stations; external weather service values come through the OpenAgri Weather path or an approved public weather source.',
            'The Weather service status shows whether the endpoint is reachable and whether live observations are available.',
            'Weather-provider credentials are configured in Settings by authorised users. Stored keys are never displayed after saving.',
            'Weather affects irrigation recommendations, evapotranspiration estimates, pest pressure and disease-risk interpretation. If credentials are missing, the platform clearly reports the requirement.',
        ],
    ],
    [
        'id' => 'irrigation',
        'title' => 'Irrigation',
        'audience' => 'Farm Manager, Advisor / Researcher',
        'items' => [
            'Irrigation recommendations combine soil moisture, weather, water balance, flow and energy context where available.',
            'Water balance and irrigation deficit indicators show whether a crop block is moving below preferred moisture thresholds.',
            'Pump flow and energy values help confirm that a recommended irrigation window is feasible and efficient.',
            'Confidence labels indicate whether inputs are current and complete. Lower confidence requires field confirmation before executing an action.',
        ],
    ],
    [
        'id' => 'pest-disease',
        'title' => 'Pest and Disease',
        'audience' => 'Farm Manager, Advisor / Researcher',
        'items' => [
            'Risk levels combine temperature, humidity, leaf wetness, rainfall and crop context. Use them as decision support, not as the only inspection source.',
            'Growing degree days, where available, support pest-stage interpretation and scouting timing.',
            'Moderate or high risk should trigger field scouting, trap checks and review of local agronomic advice.',
            'Risk outputs depend on input quality. Missing weather or stale leaf-wetness data can reduce confidence.',
        ],
    ],
    [
        'id' => 'farm-calendar',
        'title' => 'Farm Calendar',
        'audience' => 'Farm Manager, Advisor / Researcher',
        'items' => [
            'The Farm Calendar records operations, observations, assets and farm records that explain why conditions changed.',
            'Operations include irrigation checks, sensor checks, scouting, pump inspection and other farm activities.',
            'FarmCalendar integration allows OpenAgri services to share operational context when configured.',
            'Keep records concise and timestamped so reports and analytics remain auditable.',
        ],
    ],
    [
        'id' => 'reports',
        'title' => 'Reports',
        'audience' => 'Farm Manager, Advisor / Researcher, Administrator',
        'items' => [
            'Reports summarise advisory outputs for irrigation, pest and disease, sensor stations, service state and operational evidence.',
            'Export actions prepare print-ready or JSON payloads using AgriTwin, Fotovoltaica Macotera SL and OBSIDIAN branding.',
            'Reports include operational data needed for review and avoid private secrets or credentials.',
            'Check the data source labels before distributing a report externally.',
        ],
    ],
    [
        'id' => 'openagri-services',
        'title' => 'OpenAgri Services',
        'audience' => 'Technical Manager, Administrator',
        'items' => [
            'Core OpenAgri services are Gatekeeper / Access Control, Weather Data Service, Irrigation Management Service and Pest & Disease Management Service.',
            'Additional operational services are FarmCalendar and Reporting Service. Geodatabase / OCSM is optional and only affects deployments that configure it.',
            'Status meanings include Service available, Service unavailable, Configuration required, Credentials required and External provider configuration required.',
            'Endpoint links are service URLs, API documentation links describe usage, and source repository links point to software source code.',
        ],
    ],
    [
        'id' => 'settings',
        'title' => 'Settings',
        'audience' => 'Administrator, Technical Manager',
        'admin' => true,
        'items' => [
            'Settings show platform mode, OpenAgri URLs, weather preference, API base path, timezone, refresh intervals and database status.',
            'Weather API keys can be saved, validated and removed by authorised users. Secrets remain local and are never returned in page source or API responses.',
            'Service URLs can be reviewed from Settings, while sensitive credentials must remain environment-managed or in the secure local storage path.',
            'Use the database diagnostics panel to confirm host, port, database name and current connection state without exposing passwords.',
        ],
    ],
    [
        'id' => 'user-management',
        'title' => 'User Management',
        'audience' => 'Administrator',
        'admin' => true,
        'items' => [
            'Administrators can create users, assign roles, reset passwords, enable or disable accounts and set preferred language.',
            'Passwords are stored as hashes. Temporary passwords should be changed at first login.',
            'Technical Manager, Farm Manager, Advisor / Researcher and Viewer roles receive progressively narrower access.',
            'Disable unused accounts instead of deleting operational history.',
        ],
    ],
    [
        'id' => 'api-swagger',
        'title' => 'API / Swagger',
        'audience' => 'Technical Manager, Administrator, Advisor / Researcher',
        'items' => [
            'Swagger presents the OpenAPI description for AgriTwin endpoints such as health, weather, sensor stations, analytics, OpenAgri services and KPIs.',
            'Technical users can inspect request and response formats and test read-only endpoints where permissions allow.',
            'The OpenAPI JSON is intended for integration tooling and does not contain credentials.',
            'Write actions require the relevant authenticated role and CSRF protection where browser forms are used.',
        ],
    ],
    [
        'id' => 'troubleshooting',
        'title' => 'Troubleshooting',
        'audience' => 'All users, Technical Manager',
        'items' => [
            'If the database is unavailable, run scripts/start_local_stack.sh and then scripts/check_local_stack.sh. Do not delete database volumes during normal recovery.',
            'If an OpenAgri service is unavailable, run scripts/openagri/check_openagri_services.sh and review endpoint, credentials and container status.',
            'If weather credentials are required, an authorised user must configure the provider key in Settings.',
            'If charts do not load, refresh the page and confirm JavaScript is enabled. If language selection does not persist, check browser cookies for /agritwin/.',
            'For login or password issues, contact an Administrator to reset the account or confirm role access.',
        ],
    ],
    [
        'id' => 'glossary',
        'title' => 'Glossary',
        'audience' => 'All users',
        'items' => [
            'ADS: Agricultural Decision Support. API: Application Programming Interface. OpenAPI and Swagger: formats and tools for API documentation.',
            'Gatekeeper controls authentication and service access. Weather Service, Irrigation Management and Pest & Disease Management provide core OpenAgri functions.',
            'FarmCalendar records operations and observations. Sensor station means a field asset collecting multiple measurements.',
            'VWC is volumetric water content. ET0 is reference evapotranspiration. Kc is crop coefficient. NDVI and NDRE are vegetation indices.',
            'KPI means Key Performance Indicator. Local fallback means the platform is using local operational data while a live connection is unavailable. Credentials required means a configured secret is needed. Service available means the endpoint check succeeded.',
        ],
    ],
];
