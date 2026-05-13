# Security & Privacy Evidence - Agent C

Date: 2026-05-12
Agent: C Security/API
Status: IN PROGRESS

---

## Executive Summary

Farm-scope isolation is **NOT implemented** in current API controllers. This is a CRITICAL security gap. All resource controllers return data across all farms for any authenticated user.

---

## 1. RBAC Matrix Draft

### Roles Defined (from `app/Models/User.php`)

| Role | Code | Approver? | System-Wide? |
|------|------|-----------|--------------|
| Admin | `admin` | ✅ Yes | ✅ Yes |
| Farm Owner | `farm_owner` | ✅ Yes | ❌ Farm-scoped |
| Farm Manager | `farm_manager` | ✅ Yes | ❌ Farm-scoped |
| Technician | `technician` | ❌ No | ❌ Farm-scoped |
| Worker | `worker` | ❌ No | ❌ Farm-scoped |
| Warehouse | `warehouse` | ❌ No | ❌ Farm-scoped |
| Delivery | `delivery` | ❌ No | ❌ Farm-scoped |

### Permission Matrix (Planned vs Current)

| Resource | Action | admin | farm_owner | farm_manager | technician | worker |
|----------|--------|-------|------------|--------------|------------|--------|
| Farm | read | ✅ | ✅ (own) | ✅ (own) | ✅ (own) | ❌ |
| Farm | write | ✅ | ✅ (own) | ✅ (own) | ❌ | ❌ |
| Plot | read | ✅ | ✅ | ✅ | ✅ | ✅ |
| Plot | write | ✅ | ✅ | ✅ | ❌ | ❌ |
| Crop | read | ✅ | ✅ | ✅ | ✅ | ❌ |
| Crop | write | ✅ | ✅ | ✅ | ❌ | ❌ |
| SupplyContract | read | ✅ | ✅ | ✅ | ❌ | ❌ |
| SupplyContract | write | ✅ | ✅ | ✅ | ❌ | ❌ |
| ProductionPlan | read | ✅ | ✅ | ✅ | ✅ | ❌ |
| ProductionPlan | write | ✅ | ✅ | ✅ | ❌ | ❌ |
| Approval | approve | ✅ | ✅ | ✅ | ❌ | ❌ |

### Gap: RBAC Enforcement Status

- [x] Role constants defined in User model
- [x] `canApprove()` helper method
- [x] `ApprovalPolicy` exists and enforces approver roles
- [ ] **CRITICAL**: No Gate/Policy enforcement on API controllers
- [ ] **CRITICAL**: No farm scope filtering in queries

---

## 2. Farm-Scope Access Risk Review

### Current State: NO FARM ISOLATION

All API controllers are protected by `auth:sanctum` but **do not filter by farm_id**:

```php
// app/Http/Controllers/Api/V1/FarmApiController.php
public function index()
{
    return Farm::all();  // ❌ Returns ALL farms, not just user's farm
}
```

```php
// app/Http/Controllers/Api/V1/PlotApiController.php
public function index()
{
    return Plot::all();  // ❌ Returns ALL plots, not user's farm's plots
}
```

### Risk Matrix

| Risk | Severity | Status | Mitigation Required |
|------|----------|--------|---------------------|
| User A sees Farm B data | **CRITICAL** | Unmitigated | Filter all queries by `farm_id` |
| Worker sees all farms | **HIGH** | Unmitigated | Add middleware/policy |
| Planner sees competitor data | **CRITICAL** | Unmitigated | Add farm scope to planning |
| No audit trail for cross-farm access | **MEDIUM** | Unmitigated | Add logging middleware |

### Required Fix Pattern

```php
// CORRECT pattern for all controllers
public function index(Request $request)
{
    $user = $request->user();

    $query = Farm::query();

    // Farm-scoped users can only see their farm
    if (!$user->isAdmin()) {
        $query->where('id', $user->farm_id);
    }

    return $query->get();
}
```

### Recommended Test Scenarios

1. **Farm A user reads farms** → Should only see Farm A
2. **Farm A user reads plots** → Should only see Farm A's plots
3. **Farm B user reads farms** → Should only see Farm B
4. **Admin reads farms** → Should see all farms

---

## 3. Sanctum Token Policy Notes

### Current Implementation

```php
// app/Http/Controllers/Api/V1/AuthController.php
$token = $user->createToken('api-token')->plainTextToken;
```

### Issues Identified

| Issue | Severity | Current State | Recommended |
|-------|----------|---------------|-------------|
| Generic token name | LOW | `'api-token'` for all | Use `mobile-token` / `web-token` |
| No token expiration | MEDIUM | Infinite | Add `expiresAt` on creation |
| No token scopes | LOW | Not used | Add scopes for mobile/web |
| No login throttling | MEDIUM | None | Add rate limiting |
| Logout works | HIGH | ✅ Implemented | - |

### Missing Security Controls

1. **Rate Limiting**: No throttling on `/auth/login`
2. **Token Cleanup**: No scheduled task to revoke old tokens
3. **Session Tracking**: No last-used timestamp on tokens
4. **Multiple Devices**: No limit on tokens per user

### Recommended Token Policy

```php
// Recommended login response
return response()->json([
    'token' => $token,
    'token_type' => 'Bearer',
    'expires_at' => now()->addDays(7),  // Add expiration
    'scope' => 'mobile',  // or 'web'
    'user' => [
        'id' => $user->id,
        'role' => $user->role,
        'farm_id' => $user->farm_id,
    ],
]);
```

---

## 4. API Error Schema Proposal

### Current State: Inconsistent

```php
// AuthController - Basic
return response()->json(['message' => 'Invalid credentials'], 401);

// PlanningController - Domain errors
return response()->json([
    'error' => 'planning_error',
    'message' => 'Missing loss profile...',
    'field' => 'loss_profile',
], 422);
```

### Proposed Standard Error Schema

```json
{
  "error": {
    "code": "PLANNING_NORM_MISSING",
    "message": "Human-readable message in current locale",
    "details": {
      "field": "loss_profile",
      "resource_type": "crop",
      "resource_id": 123,
      "suggestion": "Configure harvest, processing, packing loss percentages"
    },
    "trace_id": "req_abc123"  // For debugging
  }
}
```

### Error Code Taxonomy

| Category | Prefix | Examples |
|----------|--------|----------|
| Validation | `VALIDATION_` | `VALIDATION_REQUIRED`, `VALIDATION_FORMAT` |
| Authorization | `AUTH_` | `AUTH_CREDENTIALS`, `AUTH_EXPIRED`, `AUTH_FORBIDDEN` |
| Resource | `RESOURCE_` | `RESOURCE_NOT_FOUND`, `RESOURCE_CONFLICT` |
| Domain/Business | `DOMAIN_` | `DOMAIN_NORM_MISSING`, `DOMAIN_FARM_SCOPE` |
| System | `SYSTEM_` | `SYSTEM_ERROR`, `SYSTEM_RATE_LIMIT` |

### HTTP Status Mapping

| Status | When to Use |
|--------|-------------|
| 400 | Bad Request (malformed) |
| 401 | Unauthenticated (no token) |
| 403 | Unauthorized (valid token, no permission) |
| 404 | Resource not found |
| 422 | Validation error / Domain error |
| 429 | Rate limit exceeded |
| 500 | System error |

---

## 5. Public QR Privacy Boundary Checklist

### Planned (Not Yet Implemented)

Based on BRD requirements, the QR endpoint (`/api/v1/traceability/{qr_code}`) will be **public** (no auth).

### Privacy Classification

| Field Category | Public QR? | Notes |
|----------------|------------|-------|
| Crop name | ✅ YES | Basic product info |
| Farm name | ✅ YES | Source transparency |
| Harvest date | ✅ YES | Traceability |
| Packing date | ✅ YES | Traceability |
| Certification | ✅ YES | Food safety |
| Grade/Quality | ✅ YES | Consumer info |
| Production stage | ✅ YES | Basic status |
| **Chemical names** | ❌ NO | Proprietary/Safety |
| **Dosage amounts** | ❌ NO | Sensitive |
| **Internal user names** | ❌ NO | Privacy |
| **Costs/Prices** | ❌ NO | Business secret |
| **Margin/Revenue** | ❌ NO | Business secret |
| **GPS coordinates** | ❌ NO | Location privacy |
| **Worker IDs** | ❌ NO | Privacy |

### Required Implementation

```php
class TraceabilityPresenter
{
    public static function publicData(PackingLot $lot): array
    {
        return [
            'crop' => $lot->harvest->batch->crop->name,
            'farm' => $lot->harvest->batch->farm->name,
            'harvest_date' => $lot->harvest->harvest_date,
            'packing_date' => $lot->packed_at,
            'certification' => $lot->farm->certification,
            'grade' => $lot->grade,
            // MUST NOT include:
            // - chemical_usages (names, dosages)
            // - costs, prices, margins
            // - user names/IDs
            // - internal notes
        ];
    }
}
```

---

## 6. Recommended Security Tests

### Priority 1: Farm Isolation Tests

```php
public function test_farm_manager_cannot_see_other_farm_plots(): void
{
    $farmA = Farm::create(['name' => 'Farm A']);
    $farmB = Farm::create(['name' => 'Farm B']);
    
    $managerA = User::factory()->create([
        'role' => User::ROLE_FARM_MANAGER,
        'farm_id' => $farmA->id,
    ]);
    
    $plotB = Plot::create(['farm_id' => $farmB->id, 'name' => 'Secret Plot']);
    
    Sanctum::actingAs($managerA);
    
    $response = $this->getJson('/api/v1/plots/' . $plotB->id);
    
    $response->assertStatus(403);  // or 404 (not found for scope)
}
```

### Priority 2: Sensitive Action Tests

```php
public function test_worker_cannot_approve(): void
{
    $worker = User::factory()->create(['role' => User::ROLE_WORKER]);
    Sanctum::actingAs($worker);
    
    $response = $this->postJson('/api/v1/approvals/1/approve');
    
    $response->assertStatus(403);
}
```

### Priority 3: Token Security Tests

```php
public function test_expired_token_returns_401(): void
{
    // Create token with past expiration
    $token = $user->createToken('test', ['*'], now()->subDay());
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/v1/me');
    
    $response->assertStatus(401);
}
```

### Priority 4: Rate Limiting Tests

```php
public function test_login_rate_limited_after_5_attempts(): void
{
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'attacker@example.com',
            'password' => 'wrong',
        ]);
    }
    
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'attacker@example.com',
        'password' => 'wrong',
    ]);
    
    $response->assertStatus(429);
}
```

---

## Open Risks / Open Questions

1. **CRITICAL**: Farm isolation not implemented - must fix before MVP-1
2. No rate limiting on auth endpoints
3. No token expiration policy
4. No audit logging for sensitive actions
5. QR endpoint not implemented yet
6. Cross-farm harvest/packing mixing not constrained

## Next Recommended Steps

1. Add `farm_id` filter to ALL API controllers
2. Create `FarmScopePolicy` middleware
3. Add rate limiting to `/auth/login`
4. Implement token expiration (7 days web, 30 days mobile)
5. Create traceability presenter with privacy whitelist
6. Add security tests for farm isolation

---

## Evidence Files Created

- `.sisyphus/evidence/final-f3c-security-privacy.md` (this file)
- `.sisyphus/evidence/api-error-contract-v1.md`
- `.sisyphus/evidence/rbac-matrix-v1.md`
