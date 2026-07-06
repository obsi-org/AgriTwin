#!/usr/bin/env python3
#
# AgriTwin - Agricultural Digital Twin for Intelligent Decision-Making
# Copyright 2026 Obsidian Innovation Institute - Associação
# Licensed under the Apache License, Version 2.0. See LICENSE for details.
# Funding and disclaimer information is provided in NOTICE.

"""Smoke tests for the safe AgriTwin AI training workflow."""

from __future__ import annotations

import json
import subprocess
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]


def run(command: list[str]) -> str:
    result = subprocess.run(command, cwd=ROOT, text=True, capture_output=True, check=True)
    return result.stdout


def main() -> int:
    dry_run = json.loads(run(["python3", "ai/train.py", "--config", "ai/config/training_config.example.json", "--dry-run"]))
    assert dry_run["ok"] is True
    assert dry_run["records"] > 100
    assert dry_run["target_variable"] == "water_stress_index"

    framework = json.loads(run(["python3", "ai/train.py", "--framework-status"]))
    assert framework["compatible_framework_available"] is True

    inference = json.loads(run(["python3", "ai/infer.py", "--model", "latest", "--input", "ai/examples/inference_input.json"]))
    assert inference["ok"] is True
    assert 0 <= float(inference["prediction"]) <= 1

    print("[PASS] AI training dry run and inference smoke test")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
