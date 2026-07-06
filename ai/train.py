#!/usr/bin/env python3
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

"""Train safe AgriTwin DSS support models from the sample dataset package."""

from __future__ import annotations

import argparse
import csv
import json
import math
import os
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


ROOT = Path(__file__).resolve().parents[1]
DEFAULT_CONFIG = ROOT / "ai/config/training_config.example.json"


def load_json(path: Path) -> dict[str, Any]:
    return json.loads(path.read_text(encoding="utf-8"))


def write_json(path: Path, payload: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def resolve_repo_path(value: str) -> Path:
    path = Path(value)
    return path if path.is_absolute() else ROOT / path


def target_value(row: dict[str, str], target: str) -> float:
    if target in row and row[target] not in ("", None):
        return float(row[target])
    soil = float(row.get("soil_moisture_vwc_pct", 24) or 24)
    humidity = float(row.get("relative_humidity_pct", 50) or 50)
    leaf = float(row.get("leaf_wetness_pct", 10) or 10)
    temp = float(row.get("air_temperature_c", 22) or 22)
    if target == "pest_disease_risk_index":
        return clamp((humidity - 35) / 80 + leaf / 180, 0, 1)
    if target == "irrigation_recommendation_score":
        return clamp((28 - soil) / 22 + max(temp - 26, 0) / 30, 0, 1)
    if target == "nitrogen_recommendation_kg_ha":
        return clamp(45 + (24 - soil) * 1.2, 0, 120)
    raise ValueError(f"Target variable is unavailable: {target}")


def clamp(value: float, lower: float, upper: float) -> float:
    return max(lower, min(upper, value))


def load_dataset(config: dict[str, Any]) -> tuple[list[list[float]], list[float]]:
    dataset = resolve_repo_path(str(config["dataset_path"]))
    if not dataset.is_file():
        raise FileNotFoundError(f"Dataset not found: {dataset.relative_to(ROOT)}")
    features = list(config["feature_columns"])
    target = str(config["target_variable"])
    rows_x: list[list[float]] = []
    rows_y: list[float] = []
    with dataset.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        for row in reader:
            try:
                rows_x.append([float(row[column]) for column in features])
                rows_y.append(float(target_value(row, target)))
            except (TypeError, ValueError, KeyError):
                continue
    if len(rows_x) < 10:
        raise RuntimeError("Training dataset has too few valid rows")
    return rows_x, rows_y


def normalise(x: list[list[float]]) -> tuple[list[list[float]], list[float], list[float]]:
    columns = list(zip(*x))
    means = [sum(col) / len(col) for col in columns]
    stds = []
    for col, mean in zip(columns, means):
        variance = sum((value - mean) ** 2 for value in col) / max(len(col) - 1, 1)
        stds.append(math.sqrt(variance) or 1.0)
    norm = [[(value - means[index]) / stds[index] for index, value in enumerate(row)] for row in x]
    return norm, means, stds


def train_fallback(
    x: list[list[float]],
    y: list[float],
    epochs: int,
    learning_rate: float,
    split: float,
) -> dict[str, Any]:
    x_norm, means, stds = normalise(x)
    cut = max(1, min(len(x_norm) - 1, int(len(x_norm) * split)))
    train_x, valid_x = x_norm[:cut], x_norm[cut:]
    train_y, valid_y = y[:cut], y[cut:]
    weights = [0.0 for _ in train_x[0]]
    bias = sum(train_y) / len(train_y)
    for _ in range(max(1, epochs)):
        for row, target in zip(train_x, train_y):
            prediction = bias + sum(weight * value for weight, value in zip(weights, row))
            error = prediction - target
            bias -= learning_rate * error
            for index, value in enumerate(row):
                weights[index] -= learning_rate * error * value
    predictions = [bias + sum(weight * value for weight, value in zip(weights, row)) for row in valid_x]
    mae = sum(abs(prediction - target) for prediction, target in zip(predictions, valid_y)) / len(valid_y)
    return {
        "framework": "tensorflow_compatible_fallback",
        "weights": weights,
        "bias": bias,
        "feature_means": means,
        "feature_stds": stds,
        "validation_mae": mae,
        "validation_records": len(valid_y),
    }


def try_tensorflow_training(
    x: list[list[float]],
    y: list[float],
    config: dict[str, Any],
    output_dir: Path,
    epochs: int,
) -> dict[str, Any] | None:
    try:
        import numpy as np  # type: ignore
        import tensorflow as tf  # type: ignore
    except Exception:
        return None
    x_norm, means, stds = normalise(x)
    split = float(config.get("train_validation_split", 0.8))
    cut = max(1, min(len(x_norm) - 1, int(len(x_norm) * split)))
    train_x = np.array(x_norm[:cut], dtype="float32")
    train_y = np.array(y[:cut], dtype="float32")
    valid_x = np.array(x_norm[cut:], dtype="float32")
    valid_y = np.array(y[cut:], dtype="float32")
    model = tf.keras.Sequential([
        tf.keras.layers.Input(shape=(train_x.shape[1],)),
        tf.keras.layers.Dense(8, activation="relu"),
        tf.keras.layers.Dense(1),
    ])
    model.compile(optimizer=tf.keras.optimizers.Adam(learning_rate=0.01), loss="mae", metrics=["mae"])
    model.fit(train_x, train_y, validation_data=(valid_x, valid_y), epochs=max(1, epochs), batch_size=int(config.get("batch_size", 32)), verbose=0)
    metrics = model.evaluate(valid_x, valid_y, verbose=0)
    output_dir.mkdir(parents=True, exist_ok=True)
    keras_path = output_dir / "latest_model.keras"
    model.save(keras_path)
    return {
        "framework": "tensorflow",
        "keras_model_path": str(keras_path.relative_to(ROOT)),
        "feature_means": means,
        "feature_stds": stds,
        "validation_mae": float(metrics[1] if isinstance(metrics, list) else metrics),
        "validation_records": int(len(valid_y)),
    }


def build_metadata(config: dict[str, Any], model: dict[str, Any], record_count: int) -> dict[str, Any]:
    return {
        "model_name": config.get("model_name", "agritwin_model"),
        "model_type": config.get("model_type", "tensorflow_dense_regressor"),
        "framework": model["framework"],
        "target_variable": config["target_variable"],
        "feature_columns": config["feature_columns"],
        "dataset_source": config["dataset_path"],
        "privacy_mode": config.get("privacy_mode", "sample_dataset_only"),
        "trained_at": datetime.now(timezone.utc).isoformat(),
        "training_records": record_count,
        "validation_mae": round(float(model["validation_mae"]), 6),
        "validation_records": model["validation_records"],
        "model": model,
    }


def append_training_run(output_dir: Path, metadata: dict[str, Any]) -> None:
    runs_path = output_dir / "training_runs.json"
    runs = []
    if runs_path.is_file():
        runs = json.loads(runs_path.read_text(encoding="utf-8"))
    runs.append({
        "model_name": metadata["model_name"],
        "framework": metadata["framework"],
        "target_variable": metadata["target_variable"],
        "trained_at": metadata["trained_at"],
        "validation_mae": metadata["validation_mae"],
        "dataset_source": metadata["dataset_source"],
    })
    write_json(runs_path, runs[-20:])


def main() -> int:
    parser = argparse.ArgumentParser(description="Train AgriTwin AI models from safe sample data.")
    parser.add_argument("--config", default=str(DEFAULT_CONFIG))
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--epochs", type=int, default=None)
    parser.add_argument("--framework-status", action="store_true")
    args = parser.parse_args()

    if args.framework_status:
        tensorflow_available = False
        try:
            import tensorflow  # type: ignore  # noqa: F401
            tensorflow_available = True
        except Exception:
            pass
        print(json.dumps({
            "tensorflow_available": tensorflow_available,
            "framework": "tensorflow" if tensorflow_available else "tensorflow_compatible_fallback",
            "compatible_framework_available": True,
        }, indent=2))
        return 0

    config = load_json(resolve_repo_path(args.config))
    x, y = load_dataset(config)
    epochs = int(args.epochs if args.epochs is not None else config.get("epochs", 3))
    output_dir = resolve_repo_path(str(config.get("output_path", "ai/models/generated")))

    if args.dry_run:
        print(json.dumps({
            "ok": True,
            "mode": "dry_run",
            "dataset_source": config["dataset_path"],
            "records": len(y),
            "target_variable": config["target_variable"],
            "feature_columns": config["feature_columns"],
            "framework": "tensorflow_or_compatible",
            "would_write_to": str(output_dir.relative_to(ROOT)),
        }, indent=2))
        return 0

    model = None
    if str(config.get("framework", "")).startswith("tensorflow"):
        model = try_tensorflow_training(x, y, config, output_dir, epochs)
    if model is None:
        model = train_fallback(x, y, epochs, float(config.get("learning_rate", 0.03)), float(config.get("train_validation_split", 0.8)))

    metadata = build_metadata(config, model, len(y))
    output_dir.mkdir(parents=True, exist_ok=True)
    write_json(output_dir / "latest_model_metadata.json", metadata)
    if model["framework"] == "tensorflow_compatible_fallback":
        write_json(output_dir / "latest_model.json", metadata)
    append_training_run(output_dir, metadata)
    print(json.dumps({k: metadata[k] for k in ["model_name", "framework", "target_variable", "trained_at", "validation_mae", "training_records"]}, indent=2))
    return 0


if __name__ == "__main__":
    os.chdir(ROOT)
    raise SystemExit(main())
