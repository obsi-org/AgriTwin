# AI API and Interface

AgriTwin exposes AI/model-training capability through safe API endpoints and a role-restricted HMI page.

## HMI

The Model Training page is visible to Administrator and Technical Manager roles. It shows:

- TensorFlow or compatible framework status.
- Dataset source.
- Model output path.
- Available model metadata.
- Latest training run and validation MAE.
- Actions to run the fixed safe training workflow and an example inference.

## API

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `/api/ai/status` | GET | Safe AI framework and configuration status. |
| `/api/ai/models` | GET | Available model metadata. |
| `/api/ai/training-runs` | GET | Recent local training-run metadata. |
| `/api/ai/train` | POST | Role-protected fixed training run. |
| `/api/ai/infer` | POST | Role-protected inference against safe numeric payload fields. |

The training and inference POST endpoints require the same authenticated session and CSRF model used by the platform HMI. They do not return secrets and do not accept arbitrary paths.

## Swagger

The OpenAPI definition documents AI status, model metadata, training and inference endpoints. Examples use only generic sample values.
