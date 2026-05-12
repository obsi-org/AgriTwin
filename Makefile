.PHONY: help setup lint test run docs audit test-openagri test-hmi

PHP_FILES := $(shell find . -name '*.php' -not -path './vendor/*')

help:
	@echo "AgriTwin local prototype tasks"
	@echo "  make setup  - Show required local services and PHP version"
	@echo "  make lint   - Run PHP syntax checks"
	@echo "  make test   - Run available safe checks"
	@echo "  make test-openagri - Run OpenAgri connector tests with mock service"
	@echo "  make test-hmi - Run HMI/PWA smoke tests"
	@echo "  make run    - Start PHP built-in server on 127.0.0.1:8080"
	@echo "  make docs   - List audit and source documentation"
	@echo "  make audit  - Run lint and show audit documents"

setup:
	@php -v | head -1
	@echo "Requires a MySQL/MariaDB database matching mysql.sql and agritwin_dashboard/db_config.php."
	@echo "Review docs/audit/00_initial_repository_audit.md before exposing the app outside localhost."

lint:
	@set -e; for file in $(PHP_FILES); do php -l "$$file"; done

test: lint test-openagri test-hmi

test-openagri:
	@set -e; \
	php -S 127.0.0.1:18080 tests/fixtures/openagri/mock_router.php >/tmp/agritwin-openagri-mock.log 2>&1 & \
	pid=$$!; \
	trap 'kill $$pid >/dev/null 2>&1 || true' EXIT; \
	sleep 1; \
	php tests/openagri_connectors/OpenAgriConnectorTest.php

test-hmi:
	@php tests/hmi/HmiSmokeTest.php

run:
	@echo "Serving on http://127.0.0.1:8080"
	@php -S 127.0.0.1:8080 -t .

docs:
	@find docs -maxdepth 3 -type f | sort

audit: lint docs
