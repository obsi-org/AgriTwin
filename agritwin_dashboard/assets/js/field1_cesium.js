(function () {
    'use strict';

    const config = window.agritwinFieldConfig || {};
    const labels = config.labels || {};
    const assetBase = config.assetBasePath || '';
    const layerEntities = {
        sensors: [],
        machinery: [],
        weather: [],
        almondTrees: [],
        pistachioTrees: [],
        solarPanels: [],
        pivot: [],
        boundaries: [],
        labels: []
    };
    const imageryLayers = {
        ndvi: null,
        laiSavi: null,
        nasaNdvi: null
    };
    let viewer = null;
    let selectedEntity = null;
    const diagnostics = {
        cesiumLoaded: false,
        viewerInitialised: false,
        terrainProvider: 'unavailable',
        tokenConfigured: !!config.cesiumToken,
        copernicusConfigured: !!config.copernicusId,
        failedAssets: 0
    };

    function label(key, fallback) {
        return labels[key] || fallback;
    }

    function asset(path) {
        if (!path) {
            return '';
        }
        if (/^https?:\/\//i.test(path) || path.startsWith('/')) {
            return path;
        }
        return assetBase + path;
    }

    function showStatus(message, severity) {
        const status = document.getElementById('fieldCesiumStatus');
        if (!status || !message) {
            return;
        }
        const item = document.createElement('div');
        item.className = `field-diagnostic field-diagnostic-${severity || 'info'}`;
        item.textContent = message;
        status.hidden = false;
        status.appendChild(item);
    }

    function updateDiagnostics() {
        const panel = document.getElementById('fieldRuntimeDiagnostics');
        if (!panel) {
            return;
        }
        const rows = [
            [label('cesiumLoaded', 'Cesium loaded'), diagnostics.cesiumLoaded ? label('yes', 'Yes') : label('no', 'No')],
            [label('viewerInitialised', 'Viewer initialised'), diagnostics.viewerInitialised ? label('yes', 'Yes') : label('no', 'No')],
            [label('terrainProvider', 'Terrain provider'), diagnostics.terrainProvider],
            [label('cesiumTokenConfigured', 'Cesium token configured'), diagnostics.tokenConfigured ? label('yes', 'Yes') : label('no', 'No')],
            [label('copernicusIdConfigured', 'Copernicus ID configured'), diagnostics.copernicusConfigured ? label('yes', 'Yes') : label('no', 'No')],
            [label('entitiesLoaded', 'Entities loaded'), viewer ? String(viewer.entities.values.length) : '0'],
            [label('failedAssets', 'Failed assets'), String(diagnostics.failedAssets)]
        ];
        panel.innerHTML = rows.map(([name, value]) => `<div><dt>${name}</dt><dd>${value}</dd></div>`).join('');
    }

    function cartesian(lon, lat, height) {
        return Cesium.Cartesian3.fromDegrees(lon, lat, height || 0);
    }

    function labelGraphics(text, offsetY) {
        return {
            text,
            font: '13px Arial, sans-serif',
            fillColor: Cesium.Color.WHITE,
            outlineColor: Cesium.Color.fromCssColorString('#173b2a'),
            outlineWidth: 3,
            style: Cesium.LabelStyle.FILL_AND_OUTLINE,
            pixelOffset: new Cesium.Cartesian2(0, offsetY || -28),
            verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
            disableDepthTestDistance: Number.POSITIVE_INFINITY,
            show: true
        };
    }

    function addEntityToLayer(layer, entity) {
        entity._fieldLayer = layer;
        layerEntities[layer].push(entity);
        if (entity.label) {
            layerEntities.labels.push(entity);
        }
        return entity;
    }

    function setLayerVisible(layer, visible) {
        if (imageryLayers[layer]) {
            imageryLayers[layer].show = visible;
            updateLegend();
            return;
        }
        if (layer === 'infoPanel') {
            const panel = document.querySelector('.field-overlay-panel');
            if (panel) {
                panel.hidden = !visible;
            }
            return;
        }
        (layerEntities[layer] || []).forEach((entity) => {
            entity.show = visible;
        });
        if (layer !== 'labels') {
            syncLabelVisibility();
        }
    }

    function syncLabelVisibility() {
        const labelToggle = document.querySelector('[data-layer="labels"]');
        const labelsVisible = !labelToggle || labelToggle.classList.contains('active');
        layerEntities.labels.forEach((entity) => {
            const parentToggle = document.querySelector(`[data-layer="${entity._fieldLayer}"]`);
            const parentVisible = !parentToggle || parentToggle.classList.contains('active');
            if (entity.label) {
                entity.label.show = labelsVisible && parentVisible;
            }
        });
    }

    async function createTerrainProvider() {
        if (!config.cesiumToken) {
            showStatus(label('configurationRequired', 'Configuration required') + ': Cesium', 'warn');
            diagnostics.terrainProvider = label('ellipsoidFallback', 'Ellipsoid fallback');
            updateDiagnostics();
            return new Cesium.EllipsoidTerrainProvider();
        }
        try {
            Cesium.Ion.defaultAccessToken = config.cesiumToken;
            if (typeof Cesium.createWorldTerrain === 'function') {
                diagnostics.terrainProvider = label('ionWorldTerrain', 'Ion world terrain');
                updateDiagnostics();
                return Cesium.createWorldTerrain();
            }
            if (typeof Cesium.createWorldTerrainAsync === 'function') {
                const provider = await Cesium.createWorldTerrainAsync();
                diagnostics.terrainProvider = label('asyncWorldTerrain', 'Async world terrain');
                updateDiagnostics();
                return provider;
            }
            if (Cesium.CesiumTerrainProvider && typeof Cesium.CesiumTerrainProvider.fromIonAssetId === 'function') {
                const provider = await Cesium.CesiumTerrainProvider.fromIonAssetId(1);
                diagnostics.terrainProvider = label('ionWorldTerrain', 'Ion world terrain');
                updateDiagnostics();
                return provider;
            }
        } catch (error) {
            showStatus(label('terrainUnavailable', 'Terrain unavailable'), 'warn');
            console.warn('AgriTwin Cesium terrain fallback active', error);
        }
        diagnostics.terrainProvider = label('ellipsoidFallback', 'Ellipsoid fallback');
        updateDiagnostics();
        return new Cesium.EllipsoidTerrainProvider();
    }

    function applyTerrainProvider() {
        if (!viewer) {
            return;
        }
        createTerrainProvider().then((provider) => {
            viewer.terrainProvider = provider;
            updateDiagnostics();
        }).catch((error) => {
            diagnostics.terrainProvider = label('ellipsoidFallback', 'Ellipsoid fallback');
            showStatus(label('terrainUnavailable', 'Terrain unavailable'), 'warn');
            console.warn('AgriTwin Cesium terrain provider failed', error);
            updateDiagnostics();
        });
    }

    function sampleTerrain(cartographics) {
        if (!cartographics.length || !viewer || !viewer.terrainProvider || viewer.terrainProvider instanceof Cesium.EllipsoidTerrainProvider) {
            return Promise.resolve(cartographics);
        }
        if (typeof Cesium.sampleTerrainMostDetailed !== 'function') {
            return Promise.resolve(cartographics);
        }
        return Cesium.sampleTerrainMostDetailed(viewer.terrainProvider, cartographics).catch((error) => {
            console.warn('AgriTwin terrain sampling failed', error);
            showStatus(label('terrainUnavailable', 'Terrain unavailable'), 'warn');
            return cartographics;
        });
    }

    function resetView() {
        if (!viewer || !config.bounds) {
            return;
        }
        const rectangle = Cesium.Rectangle.fromDegrees(
            config.bounds.west,
            config.bounds.south,
            config.bounds.east,
            config.bounds.north
        );
        viewer.camera.flyTo({ destination: rectangle, duration: 1.1 });
    }

    function addBoundary() {
        const points = (config.boundary || []).map(([lon, lat]) => cartesian(lon, lat, 0));
        if (!points.length) {
            return;
        }
        addEntityToLayer('boundaries', viewer.entities.add({
            name: label('fieldBoundary', 'Property boundary'),
            polyline: {
                positions: points,
                width: 4,
                material: Cesium.Color.fromCssColorString('#f2c94c'),
                clampToGround: true
            },
            polygon: {
                hierarchy: points,
                material: Cesium.Color.fromCssColorString('#2f6f4e').withAlpha(0.10),
                outline: false
            }
        }));
    }

    function pointEntity(layer, item, color, iconLabel, description) {
        const entity = viewer.entities.add({
            name: item.name,
            position: cartesian(item.lon, item.lat, 5),
            point: {
                pixelSize: 13,
                color,
                outlineColor: Cesium.Color.WHITE,
                outlineWidth: 2,
                disableDepthTestDistance: Number.POSITIVE_INFINITY
            },
            label: labelGraphics(iconLabel || item.name, -20),
            description
        });
        entity._normalColor = color;
        entity._selectedColor = Cesium.Color.fromCssColorString('#27ae60');
        return addEntityToLayer(layer, entity);
    }

    function sensorDescription(sensor) {
        return [
            `<strong>${sensor.name}</strong>`,
            `${label('temperature', 'Temperature')}: ${sensor.temp} C`,
            `${label('soilMoisture10', 'Soil moisture (10 cm)')}: ${sensor.moist10}%`,
            `${label('soilMoisture30', 'Soil moisture (30 cm)')}: ${sensor.moist30}%`,
            `${label('soilMoisture50', 'Soil moisture (50 cm)')}: ${sensor.moist50}%`,
            `${label('coordinates', 'Coordinates')}: ${sensor.lat.toFixed(5)}, ${sensor.lon.toFixed(5)}`
        ].join('<br>');
    }

    function machineryDescription(machine) {
        return [
            `<strong>${machine.name}</strong>`,
            `${label('status', 'Status')}: ${machine.status}`,
            `${label('coordinates', 'Coordinates')}: ${machine.lat.toFixed(5)}, ${machine.lon.toFixed(5)}`
        ].join('<br>');
    }

    function weatherDescription(station) {
        const rainfall = station.rainfall ? `${label('rainfall', 'Rainfall')}: ${station.rainfall} mm<br>` : '';
        return [
            `<strong>${station.name}</strong>`,
            `${label('status', 'Status')}: ${station.status}`,
            `${label('temperature', 'Temperature')}: ${station.temperature} C`,
            `${label('humidity', 'Humidity')}: ${station.humidity}%`,
            `${label('windSpeed', 'Wind speed')}: ${station.windSpeed} m/s`,
            rainfall + `${label('coordinates', 'Coordinates')}: ${station.lat.toFixed(5)}, ${station.lon.toFixed(5)}`
        ].join('<br>');
    }

    function addMarkers() {
        (config.sensors || []).forEach((sensor) => pointEntity('sensors', sensor, Cesium.Color.fromCssColorString('#d9534f'), sensor.name, sensorDescription(sensor)));
        (config.machinery || []).forEach((machine) => pointEntity('machinery', machine, Cesium.Color.fromCssColorString('#f2c94c'), machine.name, machineryDescription(machine)));
        (config.weatherStations || []).forEach((station) => pointEntity('weather', station, Cesium.Color.fromCssColorString('#2d9cdb'), station.name, weatherDescription(station)));
    }

    function loadTreeCsv(layer, csvPath, imagePath, width, height, fallbackName, fallbackColor) {
        fetch(asset(csvPath))
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then((csvText) => {
                const cartographics = csvText.trim().split('\n').slice(1).map((row) => {
                    const [lonStr, latStr] = row.trim().split(',');
                    return Cesium.Cartographic.fromDegrees(parseFloat(lonStr), parseFloat(latStr));
                }).filter((position) => Number.isFinite(position.longitude) && Number.isFinite(position.latitude));
                return sampleTerrain(cartographics);
            })
            .then((positions) => {
                positions.forEach((position) => {
                    const cart = Cesium.Cartesian3.fromRadians(position.longitude, position.latitude, position.height || 0);
                    addEntityToLayer(layer, viewer.entities.add({
                        name: fallbackName,
                        position: cart,
                        billboard: {
                            image: asset(imagePath),
                            width,
                            height,
                            verticalOrigin: Cesium.VerticalOrigin.BOTTOM,
                            heightReference: Cesium.HeightReference.CLAMP_TO_GROUND
                        }
                    }));
                });
            })
            .catch((error) => {
                console.warn(`AgriTwin ${layer} CSV fallback active`, error);
                diagnostics.failedAssets += 1;
                updateDiagnostics();
                showStatus(`${label('assetUnavailable', 'Asset unavailable')}: ${csvPath}`, 'warn');
                addTreeFallback(layer, fallbackName, fallbackColor);
            });
    }

    function addTreeFallback(layer, name, color) {
        const starts = layer === 'almondTrees'
            ? [[-5.2618, 40.8847], [-5.2609, 40.8870], [-5.2598, 40.8892]]
            : [[-5.2578, 40.8851]];
        starts.forEach(([startLon, startLat], blockIndex) => {
            for (let row = 0; row < 5; row += 1) {
                for (let col = 0; col < 8; col += 1) {
                    addEntityToLayer(layer, viewer.entities.add({
                        name,
                        position: cartesian(startLon + (col * 0.00022), startLat + (row * 0.00018), 2.5),
                        cylinder: {
                            length: 5,
                            topRadius: 0,
                            bottomRadius: 1.8,
                            material: color,
                            outline: true,
                            outlineColor: Cesium.Color.fromCssColorString('#214e38').withAlpha(0.45)
                        }
                    }));
                }
            }
            addEntityToLayer(layer, viewer.entities.add({
                name,
                position: cartesian(startLon + 0.0008, startLat + 0.00045, 8),
                label: labelGraphics(`${name} ${blockIndex + 1}`, -6)
            }));
        });
    }

    function addSolarPanels() {
        const model = config.assets && config.assets.solarModel;
        const cartographics = (config.solarPanels || []).map((panel) => Cesium.Cartographic.fromDegrees(panel.lon, panel.lat));
        sampleTerrain(cartographics).then((positions) => {
            (config.solarPanels || []).forEach((panel, index) => {
                const pos = positions[index] || Cesium.Cartographic.fromDegrees(panel.lon, panel.lat);
                const cart = Cesium.Cartesian3.fromRadians(pos.longitude, pos.latitude, pos.height || 0);
                const orientation = Cesium.Transforms.headingPitchRollQuaternion(
                    cart,
                    new Cesium.HeadingPitchRoll(Cesium.Math.toRadians(panel.rotation - 15 + 180), 0, 0)
                );
                const entityOptions = {
                    name: `${label('solarPanels', 'Solar panels')} ${index + 1}`,
                    position: cart,
                    orientation
                };
                if (model) {
                    entityOptions.model = {
                        uri: asset(model),
                        scale: 1.0,
                        minimumPixelSize: 0,
                        maximumScale: 1000
                    };
                } else {
                    entityOptions.box = {
                        dimensions: new Cesium.Cartesian3(4.8, 2.0, 0.15),
                        material: Cesium.Color.fromCssColorString('#263746').withAlpha(0.92),
                        outline: true,
                        outlineColor: Cesium.Color.fromCssColorString('#7fb3d5')
                    };
                }
                const entity = addEntityToLayer('solarPanels', viewer.entities.add(entityOptions));
                entity.model?.readyPromise?.catch(() => {
                    diagnostics.failedAssets += 1;
                    updateDiagnostics();
                    entity.model = undefined;
                    entity.box = {
                        dimensions: new Cesium.Cartesian3(4.8, 2.0, 0.15),
                        material: Cesium.Color.fromCssColorString('#263746').withAlpha(0.92),
                        outline: true,
                        outlineColor: Cesium.Color.fromCssColorString('#7fb3d5')
                    };
                    showStatus(`${label('assetUnavailable', 'Asset unavailable')}: ${model}`, 'warn');
                });
            });
        });
    }

    function addPivot() {
        const pivot = { lon: -5.25854965, lat: 40.8893203, rotation: -1.2 };
        sampleTerrain([Cesium.Cartographic.fromDegrees(pivot.lon, pivot.lat)]).then(([position]) => {
            const cart = Cesium.Cartesian3.fromRadians(position.longitude, position.latitude, position.height || 0);
            const orientation = Cesium.Transforms.headingPitchRollQuaternion(
                cart,
                new Cesium.HeadingPitchRoll(Cesium.Math.toRadians(pivot.rotation), 0, 0)
            );
            const entity = addEntityToLayer('pivot', viewer.entities.add({
                name: label('irrigationPivot', 'Irrigation pivot'),
                position: cart,
                orientation,
                model: {
                    uri: asset(config.assets && config.assets.pivotModel),
                    scale: 1.0,
                    minimumPixelSize: 0,
                    maximumScale: 1000
                },
                label: labelGraphics(label('irrigationPivot', 'Irrigation pivot'), -28)
            }));
            entity.model?.readyPromise?.catch(() => {
                diagnostics.failedAssets += 1;
                updateDiagnostics();
                entity.model = undefined;
                entity.cylinder = {
                    length: 18,
                    topRadius: 0.2,
                    bottomRadius: 0.4,
                    material: Cesium.Color.fromCssColorString('#2d9cdb'),
                    outline: true,
                    outlineColor: Cesium.Color.WHITE
                };
                showStatus(`${label('assetUnavailable', 'Asset unavailable')}: ${config.assets && config.assets.pivotModel}`, 'warn');
            });
        });
    }

    function addImageryLayers() {
        imageryLayers.nasaNdvi = viewer.imageryLayers.addImageryProvider(new Cesium.WebMapServiceImageryProvider({
            url: 'https://gibs.earthdata.nasa.gov/wms/epsg3857/best/wms.cgi',
            layers: 'MODIS_Terra_NDVI_8Day',
            parameters: {
                service: 'WMS',
                version: '1.3.0',
                request: 'GetMap',
                format: 'image/png',
                transparent: 'true',
                styles: '',
                crs: 'EPSG:3857'
            }
        }));
        imageryLayers.nasaNdvi.show = false;

        if (!config.copernicusId) {
            showStatus(label('copernicusRequired', 'Copernicus configuration required'), 'warn');
            return;
        }
        const rectangle = Cesium.Rectangle.fromDegrees(config.bounds.west, config.bounds.south, config.bounds.east, config.bounds.north);
        const wmsOptions = {
            url: `https://sh.dataspace.copernicus.eu/ogc/wms/${config.copernicusId}?`,
            parameters: {
                service: 'WMS',
                version: '1.3.0',
                request: 'GetMap',
                format: 'image/png',
                transparent: 'true',
                CRS: 'EPSG:3857'
            },
            rectangle,
            tilingScheme: new Cesium.WebMercatorTilingScheme(),
            enablePickFeatures: false,
            maximumLevel: 14,
            credit: 'Sentinel Hub / Copernicus'
        };
        imageryLayers.ndvi = viewer.imageryLayers.addImageryProvider(new Cesium.WebMapServiceImageryProvider({ ...wmsOptions, layers: 'NDVI' }));
        imageryLayers.laiSavi = viewer.imageryLayers.addImageryProvider(new Cesium.WebMapServiceImageryProvider({ ...wmsOptions, layers: 'LAI_SAVI' }));
        imageryLayers.ndvi.show = false;
        imageryLayers.laiSavi.show = false;
    }

    function updateLegend() {
        const legend = document.getElementById('fieldLegend');
        const image = document.getElementById('fieldLegendImage');
        const title = document.getElementById('fieldLegendTitle');
        const fallback = document.getElementById('fieldLegendFallback');
        if (!legend || !image || !title || !fallback) {
            return;
        }
        let active = null;
        if (imageryLayers.ndvi?.show) {
            active = { title: 'NDVI', image: config.assets && config.assets.ndviLegend };
        } else if (imageryLayers.laiSavi?.show) {
            active = { title: label('laiSavi', 'LAI/SAVI'), image: config.assets && config.assets.laiSaviLegend };
        }
        if (!active) {
            legend.hidden = true;
            return;
        }
        legend.hidden = false;
        title.textContent = active.title;
        fallback.hidden = true;
        image.hidden = false;
        image.src = asset(active.image);
        image.onerror = function () {
            diagnostics.failedAssets += 1;
            updateDiagnostics();
            image.hidden = true;
            fallback.hidden = false;
            fallback.textContent = `${label('assetUnavailable', 'Asset unavailable')}: ${active.image}`;
        };
    }

    function initialiseSelection() {
        viewer.selectedEntityChanged.addEventListener((entity) => {
            if (selectedEntity && selectedEntity.point) {
                selectedEntity.point.color = selectedEntity._normalColor || selectedEntity.point.color;
            }
            if (entity && entity.point) {
                entity.point.color = entity._selectedColor || Cesium.Color.fromCssColorString('#27ae60');
            }
            selectedEntity = entity;
        });
    }

    function wireControls() {
        document.getElementById('fieldResetView')?.addEventListener('click', resetView);
        document.querySelectorAll('.field-layer-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const layer = button.getAttribute('data-layer');
                if (!layer) {
                    return;
                }
                button.classList.toggle('active');
                setLayerVisible(layer, button.classList.contains('active'));
            });
        });
        document.addEventListener('keydown', (event) => {
            const keyMap = { s: 'sensors', m: 'machinery', w: 'weather', t: 'almondTrees', p: 'pistachioTrees', o: 'solarPanels', n: 'nasaNdvi' };
            const layer = keyMap[event.key.toLowerCase()];
            if (!layer) {
                return;
            }
            const button = document.querySelector(`[data-layer="${layer}"]`);
            if (button) {
                button.click();
            }
        });
    }

    function initialise() {
        const container = document.getElementById('cesiumContainer');
        if (!container) {
            return;
        }
        if (!window.Cesium) {
            diagnostics.cesiumLoaded = false;
            updateDiagnostics();
            showStatus(label('cesiumUnavailable', 'Cesium viewer unavailable'), 'error');
            return;
        }
        diagnostics.cesiumLoaded = true;
        updateDiagnostics();
        try {
            viewer = new Cesium.Viewer(container, {
                terrainProvider: new Cesium.EllipsoidTerrainProvider(),
                animation: false,
                timeline: false,
                geocoder: false,
                homeButton: false,
                sceneModePicker: true,
                baseLayerPicker: true,
                navigationHelpButton: false,
                selectionIndicator: true,
                infoBox: true
            });
            diagnostics.viewerInitialised = true;
            diagnostics.terrainProvider = label('ellipsoidFallback', 'Ellipsoid fallback');
            updateDiagnostics();
            viewer.scene.globe.depthTestAgainstTerrain = false;
            applyTerrainProvider();

            addImageryLayers();
            addBoundary();
            addMarkers();
            loadTreeCsv('almondTrees', config.assets && config.assets.almondCsv, config.assets && config.assets.almondTreeImage, 40, 50, label('almondTrees', 'Almond trees'), Cesium.Color.fromCssColorString('#4b8f42'));
            loadTreeCsv('pistachioTrees', config.assets && config.assets.pistachioCsv, config.assets && config.assets.pistachioTreeImage, 27, 32, label('pistachioTrees', 'Pistachio trees'), Cesium.Color.fromCssColorString('#7aa95c'));
            addSolarPanels();
            addPivot();
            initialiseSelection();
            wireControls();
            resetView();

            window.agritwinFieldViewer = viewer;
            window.agritwinFieldDiagnostics = {
                entityCount: () => viewer.entities.values.length,
                layers: layerEntities,
                imageryLayers,
                runtime: diagnostics
            };
            updateDiagnostics();
        } catch (error) {
            console.error('AgriTwin Cesium field view failed to initialise', error);
            showStatus(`${label('cesiumUnavailable', 'Cesium viewer unavailable')}: ${error.message || error}`, 'error');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise);
    } else {
        initialise();
    }
}());
