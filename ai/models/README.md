# Model Artefact Directory

Generated model artefacts are written under `ai/models/generated/`, which is ignored by Git. The Docker entrypoint creates the directory and makes it writable for local web-triggered training.

Commit only documentation, schema, configuration examples and small non-secret metadata. Do not commit trained model binaries, private feature stores, private thresholds or operational logs.
