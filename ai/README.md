# AgriTwin AI Model Training

This module provides a distribution-safe AI / machine-learning training workflow for AgriTwin DSS support. It is TensorFlow-first and includes a deterministic compatible fallback so training dry-runs and smoke tests can run without private data or heavy dependencies.

The default workflow reads the safe sample dataset package:

```text
datasets/agritwin_sample_dataset/synthetic_developer/
```

Generated model artefacts are written to ignored runtime paths under:

```text
ai/models/generated/
```

Do not commit trained model artefacts, private datasets, credentials, logs or site-specific thresholds.

## Commands

```bash
python3 ai/train.py --config ai/config/training_config.example.json --dry-run
python3 ai/train.py --config ai/config/training_config.example.json --epochs 1
python3 ai/infer.py --model latest --input ai/examples/inference_input.json
```

TensorFlow can be enabled by installing `ai/requirements.txt`. If TensorFlow is unavailable, the training script uses a TensorFlow-compatible linear fallback and clearly reports the framework as `tensorflow_compatible_fallback`.

## Supported Targets

- Water stress forecast.
- Pest and disease risk scoring.
- Irrigation recommendation support.
- Nitrogen/fertiliser recommendation scaffold.

All default examples use anonymised, aggregated or synthetic data only.
