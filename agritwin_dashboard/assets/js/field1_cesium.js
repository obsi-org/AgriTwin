(function () {
    'use strict';

    const config = window.agritwinFieldConfig || {};
    const labels = config.labels || {};
    const layerEntities = {
        trees: [],
        pv: [],
        sensors: [],
        irrigation: [],
        boundaries: [],
        weather: [],
        labels: []
    };

    function label(key, fallback) {
        return labels[key] || fallback;
    }

    function showStatus(message) {
        const status = document.getElementById('fieldCesiumStatus');
        if (!status) {
            return;
        }
        status.hidden = false;
        status.textContent = message;
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

    function addLabelEntity(entity) {
        if (entity && entity.label) {
            layerEntities.labels.push(entity);
        }
        return entity;
    }

    function setLayerVisible(layer, visible) {
        (layerEntities[layer] || []).forEach((entity) => {
            entity.show = visible;
        });
        if (layer !== 'labels' && layerEntities.labels.length > 0) {
            const labelToggle = document.querySelector('[data-layer="labels"]');
            const labelsVisible = !labelToggle || labelToggle.classList.contains('active');
            layerEntities.labels.forEach((entity) => {
                if (entity._fieldLayer === layer && entity.label) {
                    entity.label.show = visible && labelsVisible;
                }
            });
        }
    }

    function addBoundary(viewer) {
        const points = (config.boundary || []).map(([lon, lat]) => cartesian(lon, lat, 0));
        if (!points.length) {
            return;
        }
        const boundary = viewer.entities.add({
            name: label('fieldBoundary', 'Field boundaries'),
            polyline: {
                positions: points,
                width: 4,
                material: Cesium.Color.fromCssColorString('#f2c94c'),
                clampToGround: true
            },
            polygon: {
                hierarchy: points,
                material: Cesium.Color.fromCssColorString('#2f6f4e').withAlpha(0.12),
                outline: false
            }
        });
        boundary._fieldLayer = 'boundaries';
        layerEntities.boundaries.push(boundary);
    }

    function addTreeBlock(viewer, name, startLon, startLat, rows, cols, lonStep, latStep, color) {
        for (let row = 0; row < rows; row += 1) {
            for (let col = 0; col < cols; col += 1) {
                const lon = startLon + (col * lonStep);
                const lat = startLat + (row * latStep);
                const entity = viewer.entities.add({
                    name,
                    position: cartesian(lon, lat, 2.5),
                    cylinder: {
                        length: 5,
                        topRadius: 0,
                        bottomRadius: 1.9,
                        material: color,
                        outline: true,
                        outlineColor: Cesium.Color.fromCssColorString('#214e38').withAlpha(0.45)
                    }
                });
                entity._fieldLayer = 'trees';
                layerEntities.trees.push(entity);
            }
        }
        const labelEntity = viewer.entities.add({
            name,
            position: cartesian(startLon + (cols * lonStep * 0.48), startLat + (rows * latStep * 0.5), 8),
            label: labelGraphics(name, -6)
        });
        labelEntity._fieldLayer = 'trees';
        layerEntities.trees.push(labelEntity);
        addLabelEntity(labelEntity);
    }

    function addPvPanels(viewer) {
        const panels = [
            [-5.25722, 40.89188], [-5.25686, 40.89195], [-5.25755, 40.89181], [-5.25725, 40.89181],
            [-5.25654, 40.89208], [-5.25652, 40.89202], [-5.25720, 40.89195], [-5.25756, 40.89188],
            [-5.25686, 40.89202], [-5.25691, 40.89187], [-5.25652, 40.89195], [-5.25756, 40.89174],
            [-5.25755, 40.89195], [-5.25719, 40.89204], [-5.25683, 40.89211], [-5.25649, 40.89218]
        ];
        panels.forEach(([lon, lat], index) => {
            const position = cartesian(lon, lat, 1.2);
            const orientation = Cesium.Transforms.headingPitchRollQuaternion(
                position,
                new Cesium.HeadingPitchRoll(Cesium.Math.toRadians(166), Cesium.Math.toRadians(-8), 0)
            );
            const entity = viewer.entities.add({
                name: `${label('pvPanels', 'PV panels')} ${index + 1}`,
                position,
                orientation,
                box: {
                    dimensions: new Cesium.Cartesian3(4.8, 2.0, 0.15),
                    material: Cesium.Color.fromCssColorString('#263746').withAlpha(0.92),
                    outline: true,
                    outlineColor: Cesium.Color.fromCssColorString('#7fb3d5')
                }
            });
            entity._fieldLayer = 'pv';
            layerEntities.pv.push(entity);
        });
    }

    function addStations(viewer) {
        (config.stations || []).forEach((station) => {
            const lines = (station.readings || []).slice(0, 4).map((reading) => {
                return `${reading.label}: ${reading.value} ${reading.unit}`;
            });
            const entity = viewer.entities.add({
                name: station.id,
                position: cartesian(station.lon, station.lat, 5),
                point: {
                    pixelSize: 12,
                    color: Cesium.Color.fromCssColorString('#1f78b4'),
                    outlineColor: Cesium.Color.WHITE,
                    outlineWidth: 2,
                    disableDepthTestDistance: Number.POSITIVE_INFINITY
                },
                label: labelGraphics(station.id, -18),
                description: `<strong>${station.name}</strong><br>${station.block}<br>${station.crop}<br>${label('status', 'Status')}: ${station.status}<br>${lines.join('<br>')}`
            });
            entity._fieldLayer = 'sensors';
            layerEntities.sensors.push(entity);
            addLabelEntity(entity);
        });
    }

    function addIrrigation(viewer) {
        const lines = [
            [[-5.2617, 40.8845], [-5.2587, 40.8872], [-5.2561, 40.8901]],
            [[-5.2612, 40.8897], [-5.2591, 40.8890], [-5.2560, 40.8884]],
            [[-5.2609, 40.8862], [-5.2582, 40.8866], [-5.2557, 40.8869]]
        ];
        lines.forEach((path, index) => {
            const entity = viewer.entities.add({
                name: `${label('irrigationLine', 'Irrigation line')} ${index + 1}`,
                polyline: {
                    positions: path.map(([lon, lat]) => cartesian(lon, lat, 0)),
                    width: 3,
                    material: Cesium.Color.fromCssColorString('#2d9cdb'),
                    clampToGround: true
                }
            });
            entity._fieldLayer = 'irrigation';
            layerEntities.irrigation.push(entity);
        });

        const pumpPosition = cartesian(-5.25855, 40.88932, 2);
        const pump = viewer.entities.add({
            name: label('pumpHouse', 'Pump house'),
            position: pumpPosition,
            box: {
                dimensions: new Cesium.Cartesian3(7, 5, 4),
                material: Cesium.Color.fromCssColorString('#8d6e63').withAlpha(0.95),
                outline: true,
                outlineColor: Cesium.Color.WHITE
            },
            label: labelGraphics(label('pumpHouse', 'Pump house'), -26)
        });
        pump._fieldLayer = 'irrigation';
        layerEntities.irrigation.push(pump);
        addLabelEntity(pump);
    }

    function addWeather(viewer) {
        const mast = viewer.entities.add({
            name: label('weatherMast', 'Weather mast'),
            position: cartesian(-5.2620, 40.8865, 8),
            cylinder: {
                length: 14,
                topRadius: 0.12,
                bottomRadius: 0.2,
                material: Cesium.Color.fromCssColorString('#d9e2e8'),
                outline: true,
                outlineColor: Cesium.Color.fromCssColorString('#245a7a')
            },
            label: labelGraphics(label('weatherMast', 'Weather mast'), -30)
        });
        mast._fieldLayer = 'weather';
        layerEntities.weather.push(mast);
        addLabelEntity(mast);
    }

    function addOptionalModels(viewer) {
        const pivotUri = config.assets && config.assets.pivotModel;
        if (!pivotUri) {
            return;
        }
        try {
            const position = cartesian(-5.25855, 40.88932, 0);
            const entity = viewer.entities.add({
                name: 'Pivot 13',
                position,
                orientation: Cesium.Transforms.headingPitchRollQuaternion(
                    position,
                    new Cesium.HeadingPitchRoll(Cesium.Math.toRadians(-1.2), 0, 0)
                ),
                model: {
                    uri: pivotUri,
                    scale: 1.0,
                    maximumScale: 1000
                }
            });
            entity._fieldLayer = 'irrigation';
            layerEntities.irrigation.push(entity);
        } catch (error) {
            console.warn('AgriTwin field model could not be added', error);
        }
    }

    function resetView(viewer) {
        const rectangle = Cesium.Rectangle.fromDegrees(-5.2632, 40.8830, -5.2548, 40.8931);
        viewer.camera.flyTo({ destination: rectangle, duration: 1.1 });
    }

    function initialise() {
        const container = document.getElementById('cesiumContainer');
        if (!container) {
            return;
        }
        if (!window.Cesium) {
            showStatus(label('cesiumUnavailable', 'Cesium viewer unavailable'));
            return;
        }

        try {
            const viewerOptions = {
                animation: false,
                timeline: false,
                geocoder: false,
                homeButton: false,
                sceneModePicker: true,
                baseLayerPicker: true,
                navigationHelpButton: false,
                selectionIndicator: true,
                infoBox: true
            };
            viewerOptions.terrainProvider = new Cesium.EllipsoidTerrainProvider();

            const viewer = new Cesium.Viewer(container, viewerOptions);
            viewer.scene.globe.depthTestAgainstTerrain = false;

            addBoundary(viewer);
            addTreeBlock(viewer, label('almondBlockA', 'Almond block A'), -5.2618, 40.8847, 6, 11, 0.00022, 0.00018, Cesium.Color.fromCssColorString('#4b8f42'));
            addTreeBlock(viewer, label('almondBlockB', 'Almond block B'), -5.2609, 40.8870, 6, 10, 0.00023, 0.00018, Cesium.Color.fromCssColorString('#3f7f3b'));
            addTreeBlock(viewer, label('almondBlockC', 'Almond block C'), -5.2598, 40.8892, 5, 9, 0.00024, 0.00018, Cesium.Color.fromCssColorString('#5e9f4f'));
            addTreeBlock(viewer, label('pistachioBlockA', 'Pistachio block A'), -5.2578, 40.8851, 4, 8, 0.00023, 0.00017, Cesium.Color.fromCssColorString('#7aa95c'));
            addTreeBlock(viewer, label('maizeBlockA', 'Maize block A'), -5.2574, 40.8878, 4, 7, 0.00022, 0.00017, Cesium.Color.fromCssColorString('#8dbf45'));
            addPvPanels(viewer);
            addStations(viewer);
            addIrrigation(viewer);
            addWeather(viewer);
            addOptionalModels(viewer);
            resetView(viewer);

            document.getElementById('fieldResetView')?.addEventListener('click', () => resetView(viewer));
            document.querySelectorAll('.field-layer-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const layer = button.getAttribute('data-layer');
                    if (!layer) {
                        return;
                    }
                    button.classList.toggle('active');
                    const visible = button.classList.contains('active');
                    if (layer === 'labels') {
                        layerEntities.labels.forEach((entity) => {
                            if (entity.label) {
                                const parentToggle = document.querySelector(`[data-layer="${entity._fieldLayer}"]`);
                                entity.label.show = visible && (!parentToggle || parentToggle.classList.contains('active'));
                            }
                        });
                    } else {
                        setLayerVisible(layer, visible);
                    }
                });
            });

            window.agritwinFieldViewer = viewer;
        } catch (error) {
            console.error('AgriTwin Cesium field view failed to initialise', error);
            showStatus(`${label('cesiumUnavailable', 'Cesium viewer unavailable')}: ${error.message || error}`);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise);
    } else {
        initialise();
    }
}());
