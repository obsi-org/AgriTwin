# Third-Party Licences

The reusable AgriTwin software components created by Obsidian Innovation Institute - Associação are released under the Apache License 2.0 unless a file explicitly states otherwise.

Third-party runtimes, libraries, services and assets remain governed by their own licence terms. This file records the known integration points for audit purposes and does not replace upstream licence notices.

## Runtime and Development Dependencies

| Component | Use | Licence status |
| --- | --- | --- |
| PHP 8.x | Application runtime and tests | PHP License; provided by local or container runtime. |
| Apache HTTP Server image | Docker web runtime through the PHP Apache image | Apache-2.0; image includes additional Debian packages. |
| MySQL container image | Local Docker database service | Oracle MySQL licence terms apply to the container image. |
| CesiumJS | Environmental Digital Twin map view when configured | Apache-2.0 for CesiumJS; Cesium ion tokens are not included. |
| Font Awesome classes | Icon class names in PHP pages | Font Awesome licence applies if bundled or loaded by deployment. |
| OpenAgri services | External service integrations | Each OpenAgri repository keeps its own licence; this repository implements adapters and does not vendor upstream service code. |

## Local Assets

Project-owned AgriTwin, OpenAgri, OBSIDIAN, European Union acknowledgement and partner branding assets are used for platform and report presentation. Any third-party visual or 3D asset must be reviewed before redistribution and must keep its original licence notice where applicable.

## Internal Operational Materials

The internal operational branch may contain site-specific pilot-farm assets, coordinates, operational reports and reference material. Those materials are not part of the reusable public AgriTwin software components and must not be included in a public distribution package unless they are separately cleared and anonymised.

## Policy

- Do not alter third-party licence notices.
- Do not add AgriTwin headers to third-party/vendor files.
- Do not copy OpenAgri upstream service code into this repository unless the licence is compatible and attribution is preserved.
- Prefer adapters, service endpoints and documented integration contracts over vendoring.
