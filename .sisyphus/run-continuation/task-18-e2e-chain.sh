#!/usr/bin/env bash
set -euo pipefail

cd /Users/macbook/Herd/ariops

LOG=".sisyphus/evidence/task-18-e2e-chain.log"
: > "$LOG"

run_step() {
  local title="$1"
  shift

  {
    echo
    echo "=== ${title} ==="
    echo "$(date '+%Y-%m-%d %H:%M:%S')"
  } >> "$LOG"

  "$@" 2>&1 | rtk tee -a "$LOG"
}

echo "Task 18 dry-run chain evidence" | rtk tee -a "$LOG"

run_step "Planning demand to production plan" \
  rtk php artisan test --filter=PlanningApiTest

run_step "Planting batch lifecycle and allocation guards" \
  rtk php artisan test --filter="PlantingBatchApiTest|PlantingBatchAllocationGuardTest"

run_step "Work tasks and farming logs" \
  rtk php artisan test --filter="WorkTaskApiTest|WorkTaskLogApiTest"

run_step "Inspection and harvest eligibility" \
  rtk php artisan test --filter="PreHarvestInspectionApiTest|HarvestLotApiTest"

run_step "Packing lot and traceability" \
  rtk php artisan test --filter="PackingLotApiTest|PublicTraceabilityApiTest|TraceabilityGraphServiceTest"

run_step "Delivery return and revenue" \
  rtk php artisan test --filter=DeliveryReturnApiTest

run_step "Full suite regression" \
  rtk php artisan test

{
  echo
  echo "=== Intentional gap ==="
  echo "This dry-run uses focused API workflow tests instead of one mutable curl chain because allocation currently has no public API endpoint."
  echo "The covered slices map to demand/planning, production plan, planting batch, allocation guards, work task/log, inspection, harvest, packing/QR/traceability, delivery, return, and revenue."
} >> "$LOG"
