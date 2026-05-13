#!/usr/bin/env bash
# Task 18 Fail-Paths: Focused test runner for UAT fail-path evidence
# Usage: bash .sisyphus/run-continuation/task-18-fail-paths.sh
# Project: /Users/macbook/Herd/ariops

set -euo pipefail

cd /Users/macbook/Herd/ariops

echo "=== Task 18 Fail-Path Tests ==="
echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

echo "--- 1. Inspection Fail → Harvest Block ---"
rtk php artisan test --filter="test_failed_inspection_rejects_and_blocks_harvest|test_harvest_requires_approved_pre_harvest_inspection|test_rejected_latest_inspection_blocks_harvest|test_latest_rejected_inspection_overrides_previous_approved_inspection" --testdox 2>&1
echo ""

echo "--- 2. Isolation/Chemical Fail → Harvest Block ---"
rtk php artisan test --filter="test_active_isolation_blocks_harvest|test_isolation_guard_blocks_harvest_before_period_ends|test_harvest_eligibility_still_blocks_when_isolation_is_active|test_isolation_guard_allows_harvest_after_period_ends" --testdox 2>&1
echo ""

echo "--- 3. Delivery → Return Path ---"
rtk php artisan test --filter="test_delivery_revenue_snapshot_is_calculated_from_accepted_quantity_and_unit_price|test_return_record_updates_delivery_net_revenue_and_links_to_packing_lot|test_return_quantity_cannot_exceed_accepted_quantity" --testdox 2>&1
echo ""

echo "--- 4. Full Suite Regression ---"
rtk php artisan test 2>&1
echo ""

echo "=== All fail-path tests complete ==="
