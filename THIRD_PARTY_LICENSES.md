# Third-Party Licences

This repository is being prepared for OpenAgri open-source catalogue submission. The original AgriTwin code and documentation added by this project are licensed under Apache-2.0 unless stated otherwise.

## Runtime and Development Dependencies

| Component | Use | Licence status |
| --- | --- | --- |
| PHP 8.x | Application runtime and tests | PHP License; provided by local or container runtime. |
| Apache HTTP Server image | Docker runtime base through `php:8.3-apache` | Apache-2.0; image includes additional Debian packages. |
| MariaDB container image | Local mock demo database | GPLv2 for MariaDB server; used as external service. |
| CesiumJS | Environmental Digital Twin map view when configured | Apache-2.0 for CesiumJS; Cesium ion tokens are not included. |
| Font Awesome classes | Icon class names in legacy PHP pages | Font Awesome licence applies if bundled or loaded by deployment. |
| OpenAgri services | External service integrations | Each OpenAgri repository keeps its own licence; this repo implements adapters and does not copy service code. |

## Local Assets

The repository contains image and 3D demonstration assets inherited from the local prototype. Before a public release, project owners should confirm that each asset is either original, cleared for publication, or replaced. The publication checklist tracks this as a required manual review item.

## Source Documents

The Proposal and D1 PDFs under `docs/source/` are authoritative project documents for local engineering work. Their redistribution status must be confirmed before public publication. If not cleared, remove them from the public repository and keep only references or approved excerpts.

## Policy

- Do not alter third-party licence notices.
- Do not copy third-party OpenAgri service code into this repository unless the licence is compatible and attribution is preserved.
- Prefer adapters, service endpoints, and documented integration contracts over vendoring.
