# API Error Contract v1

Date: 2026-05-12
Agent: C Security/API
Status: DRAFT

---

## 1. Standard Error Response Schema

All API v1 endpoints MUST return errors in this structure:

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable message in current locale",
    "details": {},
    "trace_id": "req_abc123xyz"
  }
}
```

### Field Definitions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `error.code` | string | ✅ Yes | Machine-readable error code |
| `error.message` | string | ✅ Yes | Human-readable message |
| `error.details` | object | No | Additional context |
| `error.trace_id` | string | No | Request ID for debugging |

### Example: Validation Error

```json
{
  "error": {
    "code": "VALIDATION_REQUIRED",
    "message": "The quantity field is required.",
    "details": {
      "field": "quantity",
      "rule": "required"
    },
    "trace_id": "req_5f8a9b2c3d4e5f6a"
  }
}
```

### Example: Domain Error

```json
{
  "error": {
    "code": "DOMAIN_NORM_MISSING",
    "message": "Missing loss profile for crop 'Dua leo'. Configure harvest, processing, packing, grade, and reject loss percentages.",
    "details": {
      "field": "loss_profile",
      "resource_type": "crop",
      "resource_id": 42,
      "suggestion": "POST /api/v1/crops/42/loss-profile"
    },
    "trace_id": "req_5f8a9b2c3d4e5f6a"
  }
}
```

### Example: Authentication Error

```json
{
  "error": {
    "code": "AUTH_INVALID_CREDENTIALS",
    "message": "Invalid email or password.",
    "details": {},
    "trace_id": "req_5f8a9b2c3d4e5f6a"
  }
}
```

### Example: Authorization Error

```json
{
  "error": {
    "code": "AUTH_FORBIDDEN",
    "message": "You do not have permission to access this resource.",
    "details": {
      "required_role": "farm_manager",
      "current_role": "worker"
    },
    "trace_id": "req_5f8a9b2c3d4e5f6a"
  }
}
```

---

## 2. Error Code Taxonomy

### Authentication (AUTH_*)

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `AUTH_INVALID_CREDENTIALS` | 401 | Wrong email/password |
| `AUTH_TOKEN_MISSING` | 401 | No Bearer token provided |
| `AUTH_TOKEN_INVALID` | 401 | Token malformed or tampered |
| `AUTH_TOKEN_EXPIRED` | 401 | Token past expiration |
| `AUTH_FORBIDDEN` | 403 | Valid token but insufficient permissions |
| `AUTH_FARM_SCOPE` | 403 | Resource belongs to different farm |
| `AUTH_RATE_LIMITED` | 429 | Too many login attempts |

### Validation (VALIDATION_*)

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_REQUIRED` | 422 | Required field missing |
| `VALIDATION_FORMAT` | 422 | Field format invalid |
| `VALIDATION_RANGE` | 422 | Value out of allowed range |
| `VALIDATION_ENUM` | 422 | Value not in allowed options |
| `VALIDATION_TYPE` | 422 | Wrong data type |

### Resource (RESOURCE_*)

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `RESOURCE_NOT_FOUND` | 404 | Entity does not exist |
| `RESOURCE_ALREADY_EXISTS` | 409 | Duplicate unique constraint |
| `RESOURCE_CONFLICT` | 409 | State conflict (e.g., already closed) |
| `RESOURCE_GONE` | 410 | Resource permanently deleted |

### Domain/Business (DOMAIN_*)

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `DOMAIN_NORM_MISSING` | 422 | Required norm/configuration missing |
| `DOMAIN_FARM_SCOPE` | 403 | Cross-farm access attempt |
| `DOMAIN_STATE_INVALID` | 422 | Invalid state transition |
| `DOMAIN_ISOLATION` | 422 | Chemical isolation period not passed |
| `DOMAIN_SHORTAGE` | 200+ | Resource shortage (success with warning) |
| `DOMAIN_QUOTA_EXCEEDED` | 422 | User/resource quota exceeded |

### System (SYSTEM_*)

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `SYSTEM_ERROR` | 500 | Internal server error |
| `SYSTEM_MAINTENANCE` | 503 | Service under maintenance |
| `SYSTEM_RATE_LIMIT` | 429 | API rate limit exceeded |
| `SYSTEM_UNAVAILABLE` | 503 | Service temporarily unavailable |

---

## 3. HTTP Status Code Mapping

| HTTP Status | When to Use | Example |
|-------------|-------------|---------|
| 200 | Success with warnings | Planning calculation with shortage |
| 201 | Resource created | POST /farms |
| 204 | Success, no body | DELETE completed |
| 400 | Bad request | Malformed JSON |
| 401 | Unauthenticated | No/expired token |
| 403 | Unauthorized | Valid token, no permission |
| 404 | Not found | Invalid resource ID |
| 409 | Conflict | Duplicate code |
| 410 | Gone | Deleted resource |
| 422 | Validation/Domain error | Missing norm, invalid state |
| 429 | Rate limited | Too many requests |
| 500 | Server error | Unexpected exception |
| 503 | Service unavailable | Maintenance mode |

---

## 4. Success Response Schema

### Standard Success (200)

```json
{
  "data": { ... },
  "meta": {
    "trace_id": "req_abc123"
  }
}
```

### Collection with Pagination (200)

```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "trace_id": "req_abc123"
  }
}
```

### Created Resource (201)

```json
{
  "data": { ... },
  "meta": {
    "trace_id": "req_abc123"
  }
}
```

---

## 5. Implementation Example

### Trait for Controllers

```php
trait ApiResponse
{
    protected function success($data, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => ['trace_id' => request()->header('X-Trace-ID') ?? uniqid()],
        ], $status);
    }

    protected function error(string $code, string $message, array $details = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => array_filter([
                'code' => $code,
                'message' => $message,
                'details' => $details ?: null,
                'trace_id' => request()->header('X-Trace-ID') ?? uniqid(),
            ]),
        ], $status);
    }

    protected function validationError(string $field, string $message): JsonResponse
    {
        return $this->error(
            'VALIDATION_REQUIRED',
            $message,
            ['field' => $field, 'rule' => 'required'],
            422
        );
    }

    protected function domainError(string $message, array $details = []): JsonResponse
    {
        return $this->error('DOMAIN_ERROR', $message, $details, 422);
    }
}
```

### Usage in Controller

```php
class PlanningController extends Controller
{
    use ApiResponse;

    public function calculate(Request $request): JsonResponse
    {
        // Validation fails
        if (!$norm) {
            return $this->error(
                'DOMAIN_NORM_MISSING',
                "Missing loss profile for crop '{$crop->name}'. Configure harvest, processing, packing, grade, and reject loss percentages.",
                [
                    'field' => 'loss_profile',
                    'resource_type' => 'crop',
                    'resource_id' => $crop->id,
                ],
                422
            );
        }

        // Success
        return $this->success([
            'output' => $output,
            'assumptions' => $assumptions,
            'fulfillment' => $fulfillment,
        ]);
    }
}
```

---

## 6. Backward Compatibility Notes

- v1 endpoints: MUST use new error format
- Consider adding `X-API-Version` header
- Existing code should be updated incrementally
- Document migration path for v0 clients

---

## 7. Frontend Error Handling Contract

```typescript
interface ApiError {
  error: {
    code: string;
    message: string;
    details?: Record<string, unknown>;
    trace_id: string;
  };
}

// Usage
const handleApiError = (error: ApiError) => {
  switch (error.error.code) {
    case 'AUTH_TOKEN_EXPIRED':
      // Redirect to login
      break;
    case 'DOMAIN_NORM_MISSING':
      // Show configuration prompt
      break;
    case 'VALIDATION_REQUIRED':
      // Highlight field
      break;
    default:
      // Show generic message with trace_id for support
  }
};
```

---

## Files Referenced

- `app/Http/Controllers/Api/V1/AuthController.php` - needs error standardization
- `app/Http/Controllers/Api/V1/PlanningController.php` - already has domain errors
- All future API controllers must follow this contract
