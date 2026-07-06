# DSS Model Architecture

The AgriTwin DSS combines rule-based advisory logic with optional AI inference outputs. Rule-based advisories remain active when no trained model is available.

## Pipeline

1. Load safe feature data from the sample dataset or authorised local operator data.
2. Prepare numeric features for soil moisture, soil temperature, air temperature, relative humidity, leaf wetness, rainfall and solar radiation.
3. Train TensorFlow or compatible regression models for DSS target variables.
4. Store model metadata and validation metrics in ignored runtime storage.
5. Serve status, model metadata and inference through role-protected API/HMI workflows.

## Integration Points

- Irrigation advisory: can consume water-stress and irrigation-support scores.
- Pest and disease advisory: can consume humidity, leaf-wetness and rainfall risk scores.
- Analytics/KPIs: can display validation MAE and model availability.
- Reports: can include model status when report templates request DSS provenance.

## Safety

Training endpoints use fixed repository configurations and do not accept arbitrary filesystem paths. Operator data must be authorised locally and must remain outside Git.
