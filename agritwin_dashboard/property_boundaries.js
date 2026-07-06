/*
 * AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
 * Copyright 2026 Obsidian Innovation Institute - Associação
 * Licensed under the Apache License, Version 2.0. See LICENSE for details.
 * Funding and disclaimer information is provided in NOTICE.
 */

// Synthetic demo parcel geometry for open-source publication.
// Do not replace with precise pilot-farm boundaries unless explicitly cleared.
export function addPropertyBoundaries(viewer) {
  const features = [
    {
      name: "Synthetic Salamanca Demo Parcel A",
      positions: [
        -5.7000, 40.9500,
        -5.6960, 40.9500,
        -5.6960, 40.9470,
        -5.7000, 40.9470,
        -5.7000, 40.9500,
      ],
    },
    {
      name: "Synthetic Salamanca Demo Parcel B",
      positions: [
        -5.6950, 40.9495,
        -5.6910, 40.9495,
        -5.6910, 40.9465,
        -5.6950, 40.9465,
        -5.6950, 40.9495,
      ],
    },
  ];

  if (!viewer || !viewer.entities || !globalThis.Cesium) {
    return features;
  }

  for (const feature of features) {
    viewer.entities.add({
      name: feature.name,
      polygon: {
        hierarchy: globalThis.Cesium.Cartesian3.fromDegreesArray(feature.positions),
        material: globalThis.Cesium.Color.LIME.withAlpha(0.18),
        outline: true,
        outlineColor: globalThis.Cesium.Color.LIME,
      },
    });
  }

  return features;
}
