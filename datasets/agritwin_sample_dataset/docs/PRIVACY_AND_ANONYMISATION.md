# Privacy And Anonymisation

The committed dataset package applies:

- location generalisation to regional or coarse grid-cell metadata;
- parcel identifier generalisation to `parcel_group_001` style IDs;
- infrastructure identifier generalisation to generic station, pump and meter groups;
- timestamp bucketing for aggregated records;
- deterministic synthetic generation for developer records;
- exclusion of credentials, raw logs, exact private parcel geometry and site-specific thresholds.

The package must not be used to infer any real deployment layout or operational history.
