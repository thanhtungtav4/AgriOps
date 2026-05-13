# Agent V - OpenCode Go/ZEN API Contract Review

**Date:** 2026-05-13
**Agent:** Sisyphus (Agent V for AgriOps)
**Working Directory:** `/Users/macbook/Herd/ariops`

---

## Contract Assumptions Found

### API Base URL
- **React:** `/api/v1` (hardcoded in `services/api.js` and `AuthContext.jsx`)
- **Mobile:** `http://localhost:8000/api/v1` (configurable via `API_CONFIG.BASE_URL`)
- **Verdict:** Consistent v1 prefix. Mobile has hardcoded localhost default that needs env config for production.

### Auth Endpoints
| Endpoint | Method | React Usage | Backend Route |
|----------|--------|-------------|---------------|
| `/auth/login` | POST | `AuthContext.login()` | `POST /api/v1/auth/login` |
| `/auth/me` | GET | `AuthContext useEffect` | `GET /api/v1/auth/me` |
| `/auth/logout` | POST | `AuthContext.logout()` | `POST /api/v1/auth/logout` |

### Data Endpoints Used by React OperationsDashboard
| Endpoint | Method | Response Shape |
|----------|--------|----------------|
| `/alerts` | GET | `{ data: Alert[], meta }` |
| `/planting-batches` | GET | `{ data: PlantingBatch[], meta }` |
| `/work-tasks` | GET | `{ data: WorkTask[], meta }` |

All three are unpaginated `GET` returning full arrays. No query params passed by React.

### Response Wrapper
Backend uses `ApiResponse` trait: all success responses are `{ data: <payload>, meta: { trace_id } }`.
React accesses via `response.data.data` (axios auto-parses JSON, then `.data` accesses the wrapper's data field).

### Auth Token Persistence
- **Storage:** `localStorage` with key `operations_token`
- **Header:** `Authorization: Bearer ${token}`
- **Injection:** Two mechanisms:
  1. `api.js` request interceptor reads from localStorage per-request
  2. `AuthContext` sets `axios.defaults.headers.common['Authorization']` on token change
- **Redundancy:** The interceptor always overrides the default header, making the `axios.defaults` set in AuthContext effectively dead code.

### 401/Error Behavior
- **api.js interceptor:** On 401 → clears `operations_token` + `operations_user` from localStorage → `window.location.href = '/operations/login'`
- **AuthContext `/auth/me`:** On 401 → calls `logout()` → clears storage → relies on ProtectedRoute to redirect
- **Login errors:** Reads `err.response?.data?.message` for error display
- **Backend error format:** `{ error: { code, message, details, trace_id } }`

---

## Field Name Mismatches (CRITICAL)

### PlantingBatch
| Frontend Access | Backend Field | Status |
|-----------------|---------------|--------|
| `batch.code` | `code` (fillable) | ✅ Exists |
| `batch.batch_code` | N/A | ❌ Does NOT exist on model |
| `batch.crop_name` | N/A | ❌ Does NOT exist; should be `batch.crop.name` |
| `batch.crop.name` | `crop` relation (eager-loaded) | ✅ Exists |
| `batch.farm.name` | `farm` relation (eager-loaded) | ✅ Exists |
| `batch.area.name` | N/A | ❌ PlantingBatch has NO `area` relation |
| `batch.status` | `status` (fillable) | ✅ Exists |

**Impact:** `batchCode()` helper falls back safely (`batch.code || batch.batch_code || '-'`). `batch.crop_name` fallback to `batch.crop?.name` works. `batch.area?.name` always undefined — silently shows farm name instead via `batch.farm?.name`.

### WorkTask
| Frontend Access | Backend Field | Status |
|-----------------|---------------|--------|
| `task.planting_batch` | `plantingBatch` relation (camelCase) | ❌ snake_case won't match Laravel JSON |
| `task.plantingBatch` | `plantingBatch` relation (eager-loaded) | ✅ Exists |
| `task.batch` | N/A | ❌ Does NOT exist |
| `task.planned_due_date` | `planned_due_date` (fillable) | ✅ Exists |
| `task.due_date` | N/A | ❌ Does NOT exist |
| `task.priority` | `priority` (fillable, default `normal`) | ✅ Exists |
| `task.status` | `status` (fillable) | ✅ Exists |
| `task.title` | `title` (fillable) | ✅ Exists |

**Impact:** `taskBatch()` and `taskDueDate()` helpers use fallback chains. `plantingBatch` (camelCase) will match; `planting_batch` (snake_case) will be undefined. The fallback order happens to work because `plantingBatch` is checked second.

### Alert
| Frontend Access | Backend Field | Status |
|-----------------|---------------|--------|
| `alert.id` | `id` | ✅ |
| `alert.title` | `title` (fillable) | ✅ |
| `alert.message` | `message` (fillable) | ✅ |
| `alert.severity` | `severity` (fillable) | ✅ |
| `alert.farm` | `farm` relation (NOT eager-loaded in index) | ⚠️ Not loaded by controller |

**Impact:** Alert index does NOT eager-load `farm` or `recipientUser` relations. If React ever accesses `alert.farm`, it will be `null`/undefined. Currently React only uses `title`, `message`, `severity`, `id` — all safe.

---

## Auth Response Shape Verification

### Login Response
Backend returns:
```json
{
  "data": {
    "token": "...",
    "token_type": "Bearer",
    "expires_at": "2026-05-20T...",
    "user": { "id", "name", "email", "role", "farm_id", "farm" }
  },
  "meta": { "trace_id": "..." }
}
```

React destructures: `const { token, user: userData } = response.data.data`
**Verdict:** ✅ Correct. `token` and `user` are at the top level of the inner `data` object.

### /auth/me Response
Backend returns:
```json
{
  "data": {
    "id", "name", "email", "role", "is_admin", "can_approve",
    "farm_id", "farm", "scope", "last_login_at"
  },
  "meta": { "trace_id": "..." }
}
```

React sets: `setUser(response.data.data)`
**Verdict:** ✅ Correct. User fields are directly in the `data` object.

---

## Priority Value Mismatch

| Source | Values |
|--------|--------|
| Backend `WorkTask::PRIORITIES` | `low`, `normal`, `high`, `urgent` |
| React `PRIORITY_ORDER` | `urgent: 1, high: 2, normal: 3, low: 4` |
| Mobile `WorkTask.priority` type | `low`, `medium`, `high` |

**Verdict:** React matches backend. Mobile uses `medium` instead of `normal` and lacks `urgent` — this is a mobile-side issue, not a React contract issue.

---

## Risks for Expo Reuse

### HIGH RISK
1. **No token refresh mechanism** — React relies on 7-day token expiry with no refresh. Expo should implement `/auth/refresh` endpoint (backend route exists at `POST /api/v1/auth/refresh` in mobile auth service but NOT in backend routes).
   - **Action needed:** Backend needs a refresh token endpoint before Expo can reuse safely.

2. **Hardcoded localStorage key** — `operations_token` is web-specific. Expo must use `AsyncStorage` with its own key (`@ariops:auth_token`).

### MEDIUM RISK
3. **No pagination** — All three data endpoints return full arrays. Works for current data volume but will degrade. Expo should request paginated endpoints or implement client-side virtualization.

4. **Field name fallback chains** — React uses defensive access patterns (`task.planting_batch || task.plantingBatch || task.batch`). Expo should NOT copy these — they mask API inconsistencies. Use the correct field names directly.

5. **Missing eager-loaded relations** — Alert index doesn't load `farm`/`recipientUser`. If Expo needs these, backend must add `->with([...])`.

### LOW RISK
6. **`batch.area.name` always undefined** — Silent failure, falls back to `farm.name`. Cosmetic only.
7. **`axios.defaults` dead code** — AuthContext's global header setting is overridden by interceptor. Harmless but confusing.
8. **No error UI for data fetch failures** — `console.error` only. User sees empty/ stale data on failure.

---

## Recommended API Client Conventions for Expo

1. **Single API client instance** — Use the mobile app's existing `api.ts` config with `API_CONFIG.BASE_URL`. Do not create a second client.

2. **Token storage** — Use `AsyncStorage` with key `@ariops:auth_token`. Do NOT use `operations_token`.

3. **Request interceptor** — Attach `Authorization: Bearer ${token}` per-request from AsyncStorage. Do NOT use global defaults.

4. **401 interceptor** — Clear auth state, navigate to login. Match existing mobile `authService` pattern.

5. **Response type safety** — Define TypeScript interfaces matching backend response shapes. Do NOT use fallback chains for field names.

6. **Implement token refresh** — Add `/auth/refresh` backend endpoint and client-side refresh logic before 7-day expiry.

7. **Use correct relation names** — Backend uses camelCase for JSON relations (`plantingBatch`, `assignedUser`, `growthStage`). Do NOT use snake_case.

---

## Backend Changes Needed Before Task 16

| Change | Priority | Reason |
|--------|----------|--------|
| Add `POST /api/v1/auth/refresh` endpoint | HIGH | Expo needs token refresh; React doesn't but should |
| Add pagination to `/alerts`, `/planting-batches`, `/work-tasks` | MEDIUM | Will be needed as data grows |
| Eager-load `farm` on Alert index | LOW | Relation defined but not loaded; may be needed by Expo |
| Remove `batch_code` fallback from frontend | LOW | Field doesn't exist; cleanup only |

---

## Summary

**API v1 consistency:** ✅ React uses `/api/v1` consistently across all endpoints.

**Auth flow:** ✅ Login, me, logout all work correctly with proper response shape handling.

**Data contracts:** ⚠️ Mostly correct but with defensive fallback chains that mask minor field name mismatches. The fallbacks happen to work but should not be copied to Expo.

**Expo readiness:** ⚠️ Blocked on token refresh endpoint. Otherwise the three data endpoints (`/alerts`, `/planting-batches`, `/work-tasks`) are reusable as-is.
