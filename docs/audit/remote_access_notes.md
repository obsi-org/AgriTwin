# Remote Access Notes

Audit date: 2026-05-12

Network checks were performed with `curl -L --max-time 15 -I` because `gh` was not available in the local PATH.

| URL | Result |
| --- | --- |
| `https://github.com/openagri-eu/` | HTTP 200 reachable |
| `https://github.com/orgs/openagri-eu/repositories` | HTTP 200 reachable |
| `https://github.com/openagri-eu/farmcalendar/issues` | HTTP 301 redirect to `https://github.com/agstack/OpenAgri-FarmCalendar/issues` |
| `https://horizon-openagri.eu/open-source-catalogue/` | HTTP 200 reachable |

No remote repository content was copied into this local repository during the audit. The repository remains self-contained around the existing code, local assets, SQL seed, and authoritative PDFs in `docs/source/`.

