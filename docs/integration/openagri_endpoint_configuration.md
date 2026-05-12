# OpenAgri Endpoint Configuration

## URL Types

- Service endpoint: base URL used for live API calls.
- Health endpoint: service-specific health URL or path.
- API documentation: Swagger/OpenAPI documentation URL.
- Source repository: GitHub/source-code reference only; it is not treated as a live API endpoint.

## Configuration

Use environment variables such as `OPENAGRI_WEATHER_URL`, `OPENAGRI_WEATHER_HEALTH_URL` and `OPENAGRI_WEATHER_DOCS_URL`. Credentials such as `OPENAGRI_GATEKEEPER_CLIENT_SECRET` must remain outside git.

## Connection States

AgriTwin reports professional states: Live healthy, Local fallback active, Configuration required, Endpoint unreachable, Authentication failed and Connection unavailable. A service is marked live only after a real HTTP request succeeds.

## Verification

Use the OpenAgri Services page or:

```bash
curl -fsS http://localhost/agritwin3/api/openagri/services
curl -fsS -X POST 'http://localhost/agritwin3/api/openagri/services/test?service=weather'
```
