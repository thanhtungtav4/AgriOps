# RBAC Matrix v1

Date: 2026-05-12
Agent: C Security/API
Status: DRAFT - Needs Integrator Review

---

## 1. Role Definitions

| Role | Code | Description | Scope |
|------|------|-------------|-------|
| Administrator | `admin` | System-wide admin | Global |
| Farm Owner | `farm_owner` | Owner of farm operations | Farm |
| Farm Manager | `farm_manager` | Day-to-day farm management | Farm |
| Technician | `technician` | Agricultural technical staff | Farm |
| Worker | `worker` | Field workers, labor | Farm |
| Warehouse | `warehouse` | Storage and inventory | Farm |
| Delivery | `delivery` | Transportation and delivery | Farm |

---

## 2. Permission Matrix

### Resource: Farm

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| farm:read:all | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| farm:read:own | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| farm:create | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| farm:update | ✅ | ✅ (own) | ❌ | ❌ | ❌ | ❌ | ❌ |
| farm:delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| farm:settings | ✅ | ✅ (own) | ❌ | ❌ | ❌ | ❌ | ❌ |

### Resource: Plot / Bed

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| plot:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| plot:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| plot:update | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| plot:delete | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| bed:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| bed:allocate | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Crop / Variety / Norms

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| crop:read | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| crop:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| crop:update | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| variety:read | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| variety:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| norm:read | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| norm:create | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| norm:update | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Supply Contract / Demand

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| contract:read | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| contract:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| contract:update | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| contract:approve | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| demand:read | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| demand:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Production Plan

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| plan:read | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| plan:calculate | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| plan:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| plan:approve | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| plan:update | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Planting Batch

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| batch:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| batch:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| batch:transition | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| batch:allocate | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Work Task

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| task:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| task:create | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| task:assign | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| task:accept | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| task:complete | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| task:log:create | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| task:log:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |

### Resource: Incident / Chemical Usage

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| incident:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| incident:create | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| chemical:read | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| chemical:create | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| chemical:approve | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Harvest

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| harvest:read | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| harvest:inspect | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| harvest:approve | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| harvest:create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Packing

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| packing:read | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| packing:create | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| packing:publish | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| qr:generate | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### Resource: Delivery

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| delivery:read | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |
| delivery:create | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| delivery:confirm | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| delivery:return | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ |

### Resource: Approval Gate

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| approval:approve | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| approval:reject | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| approval:override | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

### Resource: User Management

| Action | admin | farm_owner | farm_manager | technician | worker | warehouse | delivery |
|--------|-------|------------|--------------|------------|--------|-----------|----------|
| user:read | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| user:create | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| user:update:role | ✅ | ✅ (own farm) | ❌ | ❌ | ❌ | ❌ | ❌ |
| user:delete | ✅ | ✅ (own farm) | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 3. Approver Roles

Roles allowed to approve sensitive actions:

- `admin`
- `farm_owner`
- `farm_manager`

**Critical**: `technician`, `worker`, `warehouse`, `delivery` CANNOT approve.

---

## 4. Sensitive Actions Requiring Approval

| Action | Requires Approval | Approver Roles |
|--------|-------------------|----------------|
| Update production norm | ✅ | admin, farm_owner |
| Approve harvest | ✅ | admin, farm_owner, farm_manager |
| Override harvest eligibility | ✅ | admin only |
| Publish packing lot | ✅ | admin, farm_owner, farm_manager |
| Confirm delivery | ✅ | admin, farm_owner, farm_manager |
| Chemical usage approval | ✅ | admin, farm_owner, farm_manager |
| Delete planting batch | ✅ | admin, farm_owner |

---

## 5. Farm Scope Enforcement

### Global Users (no farm restriction)
- `admin`

### Farm-Scoped Users (must filter by `farm_id`)
- `farm_owner`
- `farm_manager`
- `technician`
- `worker`
- `warehouse`
- `delivery`

**IMPORTANT**: All queries for farm-scoped users MUST include:
```php
// For farm-scoped roles
if (!$user->isAdmin()) {
    $query->where('farm_id', $user->farm_id);
}
```

---

## 6. Implementation Status

### Current (from code review)

| Component | Status | Notes |
|-----------|--------|-------|
| Role constants | ✅ Done | User::ROLES |
| Approver constant | ✅ Done | User::APPROVER_ROLES |
| ApprovalPolicy | ✅ Done | Only checks role |
| Controller enforcement | ❌ Missing | No farm filtering |
| Middleware | ❌ Missing | No scope middleware |

### Required Implementation

1. **FarmScopeMiddleware** - Auto-filter queries by user's farm_id
2. **Permission Gate** - Centralized permission check
3. **Policy methods** - Per-resource policies following Laravel patterns
4. **Controller updates** - Use scope middleware

---

## 7. Override Governance Matrix

| Override Action | Who Can Override | Conditions | Audit Required |
|-----------------|------------------|------------|----------------|
| Harvest eligibility | admin only | Emergency reason | ✅ Yes |
| Isolation period | admin, farm_owner | Safety inspection | ✅ Yes |
| Quality standard | admin, farm_owner, farm_manager | Customer requirement | ✅ Yes |
| Plan modification | admin, farm_owner | Re-planning needed | ✅ Yes |

All overrides must include:
- Reason (required text)
- Override by (user_id)
- Override at (timestamp)
- Review date (if applicable)

---

## Files Referenced

- `app/Models/User.php` - Role definitions
- `app/Policies/ApprovalPolicy.php` - Approval enforcement
- `app/Http/Controllers/Api/V1/*` - Controllers needing scope

---

## Next Steps

1. Create `FarmScopeMiddleware`
2. Update all controllers to use scope middleware
3. Create Laravel Policies per resource
4. Add authorization tests
5. Document permission inheritance
