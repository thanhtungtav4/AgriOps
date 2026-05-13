# MVP-0 Smoke Test Run Evidence

**Generated:** 2026-05-12
**Agent:** E - QA/Evidence
**Project:** /Users/macbook/Herd/ariops

---

## Test Run Summary

```bash
$ php artisan test
✓ 9 tests, 52 assertions, passed in 255ms
```

### PlanningApiTest (5 tests, 52 assertions)
- `test_planning_calculation_returns_core_outputs_for_complete_norms` - PASS
- `test_planning_calculation_returns_domain_error_when_norms_are_missing` - PASS
- `test_happy_path_includes_assumptions_and_fulfillment` - PASS
- `test_plant_execution_quantities_round_up` - PASS
- `test_unsupported_unit_returns_field_level_planning_error` - PASS

### SupplyInputApiTest (2 tests)
- `test_can_create_supply_contract_and_demand_inputs` - PASS
- `test_supply_contract_rejects_invalid_date_range` - PASS

### ExampleTest (2 tests)
- Basic auth and example tests - PASS

---

## Routes Registered (20 API v1 routes)

```
 POST api/v1/auth/login                AuthController@login
 POST api/v1/auth/logout               AuthController@logout
 GET  api/v1/auth/me                  AuthController@me
 GET  api/v1/crop-varieties           CropVarietyApiController@index
 GET  api/v1/crop-varieties/{id}     CropVarietyApiController@show
 GET  api/v1/crops                   CropApiController@index
 GET  api/v1/crops/{id}              CropApiController@show
 GET  api/v1/farms                   FarmApiController@index
 GET  api/v1/farms/{id}              FarmApiController@show
 POST api/v1/planning/calculate       PlanningController@calculate
 GET  api/v1/plots                   PlotApiController@index
 GET  api/v1/plots/{id}              PlotApiController@show
 POST api/v1/production-plans        PlanningController@store
 GET  api/v1/production-plans        PlanningController@index
 GET  api/v1/supply-contracts        SupplyContractController@index
 POST api/v1/supply-contracts        SupplyContractController@store
 GET  api/v1/supply-contracts/{id}   SupplyContractController@show
 GET  api/v1/supply-demands          SupplyDemandController@index
 POST api/v1/supply-demands          SupplyDemandController@store
 GET  api/v1/supply-demands/{id}     SupplyDemandController@show
```

---

## Migration Status (18 migrations)

All 18 migrations show `[1] Ran` on PostgreSQL:

```
0000_01_01_000000_create_farms_table
0001_01_01_000000_create_users_table
0001_01_01_000001_create_cache_table
0001_01_01_000002_create_jobs_table
2026_05_11_174514_create_crops_table
2026_05_11_174515_create_beds_table
2026_05_11_174515_create_crop_varieties_table
2026_05_11_174515_create_plots_table
2026_05_11_174516_create_growth_stages_table
2026_05_11_174517_create_product_standards_table
2026_05_11_174518_create_harvest_models_table
2026_05_11_174519_create_irrigation_norms_table
2026_05_11_174520_create_fertilizer_norms_table
2026_05_11_174521_create_labor_norms_table
2026_05_11_174522_create_loss_profiles_table
2026_05_12_000211_create_supply_contracts_table
2026_05_12_000211_create_supply_demands_table
2026_05_12_000213_create_production_plans_table
```

---

## Known Gaps Identified

1. **Farm scope isolation** - NOT implemented (any user sees all farms)
2. **RBAC controller enforcement** - ApprovalPolicy exists but not called by controllers
3. **Token expiry** - Not implemented
4. **Rate limiting** - Not implemented on login endpoint

See `release-risk-log.md` for full risk assessment.

---

**End of MVP-0 Smoke Test Run Evidence**
