# Validation Guide

Run the validator from repository root:

```bash
python3 datasets/agritwin_sample_dataset/validation/validate_dataset.py
```

The validator checks required files, JSON syntax, CSV headers, numeric ranges, timestamp formats, forbidden private strings and common credential patterns. It exits non-zero if unsafe content is detected.
