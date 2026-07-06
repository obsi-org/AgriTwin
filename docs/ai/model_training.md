# AI Model Training

AgriTwin provides a local AI/model-training workflow for DSS support models. The default workflow is TensorFlow-first and uses a compatible local training path when TensorFlow is unavailable.

## Default Command

```bash
python3 ai/train.py --config ai/config/training_config.example.json --dry-run
python3 ai/train.py --config ai/config/training_config.example.json --epochs 1
python3 ai/infer.py --model latest --input ai/examples/inference_input.json
```

The Docker AI profile can run the same workflow without changing the web container:

```bash
docker compose --profile ai run --rm agritwin-ai python ai/train.py --config ai/config/training_config.example.json --dry-run
```

## Data Source

The default configuration reads:

```text
datasets/agritwin_sample_dataset/synthetic_developer/synthetic_sensor_timeseries.csv
```

This committed dataset is synthetic and distribution-safe. It does not include raw pilot-farm data, exact private coordinates, credentials, site-specific infrastructure identifiers or non-anonymised logs.

## Model Artefacts

Generated model metadata and trained artefacts are written to:

```text
ai/models/generated/
```

This directory is ignored by Git. Do not commit trained models unless a separate review confirms that the artefact contains no private training data, identifiers or deployment-specific thresholds.

## Targets

- Water stress forecast.
- Pest and disease risk scoring.
- Irrigation recommendation support.
- Nitrogen/fertiliser recommendation scaffold.

## Licence

Training scripts and reusable AI code are Apache-2.0. Sample dataset records are CC BY 4.0. Third-party AI frameworks remain under their own licences.
