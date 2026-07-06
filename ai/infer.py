#!/usr/bin/env python3
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

"""Run safe inference against an AgriTwin AI model metadata file."""

from __future__ import annotations

import argparse
import json
import os
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
DEFAULT_MODEL = ROOT / "ai/models/generated/latest_model_metadata.json"


def load_payload(args: argparse.Namespace) -> dict[str, Any]:
    if args.input_json:
        return json.loads(args.input_json)
    if args.input:
        path = Path(args.input)
        path = path if path.is_absolute() else ROOT / path
        return json.loads(path.read_text(encoding="utf-8"))
    return json.loads((ROOT / "ai/examples/inference_input.json").read_text(encoding="utf-8"))


def load_model(model_arg: str) -> dict[str, Any] | None:
    path = DEFAULT_MODEL if model_arg == "latest" else Path(model_arg)
    path = path if path.is_absolute() else ROOT / path
    if not path.is_file():
        return None
    return json.loads(path.read_text(encoding="utf-8"))


def fallback_rule(payload: dict[str, Any]) -> float:
    soil = float(payload.get("soil_moisture_vwc_pct", 24))
    temp = float(payload.get("air_temperature_c", 22))
    humidity = float(payload.get("relative_humidity_pct", 50))
    leaf = float(payload.get("leaf_wetness_pct", 10))
    return max(0.0, min(1.0, (28 - soil) / 24 + max(temp - 27, 0) / 35 - max(humidity - 65, 0) / 200 + leaf / 250))


def predict_with_metadata(model: dict[str, Any], payload: dict[str, Any]) -> float:
    model_data = model.get("model", {})
    if model_data.get("framework") != "tensorflow_compatible_fallback":
        return fallback_rule(payload)
    features = model.get("feature_columns", [])
    means = model_data.get("feature_means", [])
    stds = model_data.get("feature_stds", [])
    weights = model_data.get("weights", [])
    bias = float(model_data.get("bias", 0))
    values = []
    for index, feature in enumerate(features):
        raw = float(payload.get(feature, means[index] if index < len(means) else 0))
        std = float(stds[index] if index < len(stds) else 1) or 1.0
        mean = float(means[index] if index < len(means) else 0)
        values.append((raw - mean) / std)
    prediction = bias + sum(float(weight) * value for weight, value in zip(weights, values))
    target = str(model.get("target_variable", "water_stress_index"))
    if target.endswith("_index") or target.endswith("_score"):
        return max(0.0, min(1.0, prediction))
    return prediction


def main() -> int:
    parser = argparse.ArgumentParser(description="Run AgriTwin AI inference from a safe model artefact.")
    parser.add_argument("--model", default="latest")
    parser.add_argument("--input")
    parser.add_argument("--input-json")
    args = parser.parse_args()

    payload = load_payload(args)
    model = load_model(args.model)
    prediction = predict_with_metadata(model, payload) if model else fallback_rule(payload)
    response = {
        "ok": True,
        "model_loaded": model is not None,
        "framework": model.get("framework") if model else "rule_based_fallback",
        "target_variable": model.get("target_variable") if model else "water_stress_index",
        "prediction": round(float(prediction), 6),
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "data_policy": "sample_or_operator_supplied_payload_only",
    }
    print(json.dumps(response, indent=2))
    return 0


if __name__ == "__main__":
    os.chdir(ROOT)
    raise SystemExit(main())
