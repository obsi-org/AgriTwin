.PHONY: help setup lint test run docs audit test-openagri test-hmi test-kpi test-edge test-analytics collect-kpis export-kpis compare-ads generate-d1-evidence scan-secrets publication-check compose-up

PHP_FILES := $(shell find . -name '*.php' -not -path './vendor/*')

help:
	@echo "AgriTwin local prototype tasks"
	@echo "  make setup  - Show required local services and PHP version"
	@echo "  make lint   - Run PHP syntax checks"
	@echo "  make test   - Run available safe checks"
	@echo "  make test-openagri - Run OpenAgri connector tests with mock service"
	@echo "  make test-hmi - Run HMI/PWA smoke tests"
	@echo "  make test-kpi - Run D1 KPI calculation tests"
	@echo "  make test-edge - Run edge gateway buffering/sync tests"
	@echo "  make test-analytics - Run analytics calculation tests"
	@echo "  make collect-kpis - Seed synthetic KPI events into the local JSONL store"
	@echo "  make export-kpis - Export KPI summary to JSON and CSV"
	@echo "  make compare-ads - Generate cloud-only vs mixed ADS comparison report"
	@echo "  make generate-d1-evidence - Generate all sample D1 KPI evidence artefacts"
	@echo "  make scan-secrets - Run conservative publication safety scan"
	@echo "  make publication-check - Run tests plus publication safety scan"
	@echo "  make run    - Start Docker Compose local stack on http://localhost:8083/agritwin/"
	@echo "  make compose-up - Build and start Docker Compose services"
	@echo "  make docs   - List audit and source documentation"
	@echo "  make audit  - Run lint and show audit documents"

setup:
	@php -v | head -1
	@echo "Requires a MySQL/MariaDB database matching mysql.sql and agritwin_dashboard/db_config.php."
	@echo "Review docs/audit/00_initial_repository_audit.md before exposing the app outside localhost."

lint:
	@set -e; for file in $(PHP_FILES); do php -l "$$file"; done

test: lint test-openagri test-hmi test-kpi test-edge test-analytics

test-openagri:
	@set -e; \
	php -S 127.0.0.1:18080 tests/fixtures/openagri/mock_router.php >/tmp/agritwin-openagri-mock.log 2>&1 & \
	pid=$$!; \
	trap 'kill $$pid >/dev/null 2>&1 || true' EXIT; \
	sleep 1; \
	php tests/openagri_connectors/OpenAgriConnectorTest.php

test-hmi:
	@php tests/hmi/HmiSmokeTest.php

test-kpi:
	@php tests/kpi/KpiCalculatorTest.php

test-edge:
	@php tests/edge_gateway/EdgeGatewayTest.php

test-analytics:
	@php tests/analytics/StatisticsEngineTest.php

scan-secrets:
	@php scripts/scan-publication-safety.php

publication-check: test scan-secrets

compose-up:
	@scripts/start_local_stack.sh

collect-kpis:
	@php scripts/agritwin-kpi.php collect-kpis

export-kpis:
	@php scripts/agritwin-kpi.php export-kpis --format=json --output=exports/kpi/kpi_summary.json
	@php scripts/agritwin-kpi.php export-kpis --format=csv --output=exports/kpi/kpi_summary.csv

compare-ads:
	@php scripts/agritwin-kpi.php compare-ads cloud mixed

generate-d1-evidence:
	@php scripts/agritwin-kpi.php generate-d1-evidence

run:
	@scripts/start_local_stack.sh

docs:
	@find docs -maxdepth 3 -type f | sort

audit: lint docs
