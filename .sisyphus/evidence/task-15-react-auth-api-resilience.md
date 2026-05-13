# Task 15: React Auth API Resilience - Evidence

## Files Changed

### 1. `resources/js/context/AuthContext.jsx`

**Changes:**
- Added `useRef` import for tracking logout state
- Added `LOGIN_PATH` constant (`/operations/login`)
- Added `safeJsonParse()` helper - safely parses localStorage user JSON, returns `null` on corruption
- Added `extractErrorMessage()` helper - normalizes errors from both `error.message` and legacy `message` fields, handles network errors with Vietnamese text
- Modified user state initialization to use `safeJsonParse()` instead of direct `JSON.parse()`
- Modified `logout()` to:
  - Clear axios Authorization header via `clearAuthToken()` first
  - Clear localStorage tokens immediately
  - Set `isLoggingOut.current = true` to prevent race conditions
- Modified `useEffect` (token sync) to use `isLoggingOut` ref to prevent clearing token during logout flow
- Modified `login()` error handling to use `extractErrorMessage()` with Vietnamese error messages

**Key code added:**
```javascript
const safeJsonParse = (jsonString, fallback = null) => {
    try {
        return JSON.parse(jsonString);
    } catch {
        return fallback;
    }
};

const extractErrorMessage = (err) => {
    if (!err.response) {
        return 'Không thể kết nối máy chủ. Vui lòng thử lại.';
    }
    const data = err.response.data;
    return data?.error?.message || data?.message || data?.error || `Lỗi ${err.response.status}: Vui lòng thử lại.`;
};
```

### 2. `resources/js/services/api.js`

**Changes:**
- Added `USER_KEY` constant for consistency
- Added `LOGIN_PATH` constant
- Modified 401 interceptor to check `currentPath !== LOGIN_PATH` before redirecting (prevents infinite loop on login page)
- Added `delete axios.defaults.headers.common['Authorization']` to clear axios header on 401

**Key code added:**
```javascript
if (error.response?.status === 401) {
    const currentPath = window.location.pathname;
    if (currentPath !== LOGIN_PATH) {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
        delete axios.defaults.headers.common['Authorization'];
        window.location.href = LOGIN_PATH;
    }
}
```

### 3. `resources/js/pages/LoginPage.jsx`

**Changes:**
- Added `useEffect` import
- Modified component to destructure `error` and `clearError` from `useAuth()`
- Added `useEffect` to clear error on unmount (cleanup)
- Added `useEffect` to sync AuthContext `error` to `localError` for display
- Removed hardcoded "Email hoặc mật khẩu không đúng" error - now shows API error messages

**Key code added:**
```javascript
useEffect(() => {
    return () => clearError();
}, [clearError]);

useEffect(() => {
    if (error) {
        setLocalError(error);
    }
}, [error]);
```

## Verification Commands

### Build
```
rtk npm run build
```
**Result:** `✓ built in 704ms` - Exit code 0

### Tests
```
rtk php artisan test tests/Feature/OperationsWebRoutesTest.php
```
**Result:** `5 passed, 10 assertions, 239ms` - All tests pass

## Behavior Summary

1. **Error Normalization**: Both `data.error.message` and legacy `data.message` formats are supported
2. **Safe JSON Parsing**: Corrupted localStorage gracefully returns `null` instead of throwing
3. **Logout Clears Everything**: Axios headers + localStorage cleared on logout
4. **No Infinite Loops**: 401 on login page redirects back to itself without infinite loop
5. **Vietnamese UX**: All error messages in Vietnamese, compact form kept

## Remaining Risks

1. **Race Condition on Rapid Logout/Login**: While `isLoggingOut` ref prevents token clearing during logout, rapid state changes could still cause timing issues. Lower priority.

2. **Network Error Messages**: When server unreachable, `extractErrorMessage` returns generic Vietnamese message. Could add retry option.

3. **Token Expiry Handling**: If user is on a protected page and token expires, they get redirected to login. No "session expired" message shown before redirect.

4. **No Refresh Token Mechanism**: Currently only Bearer token stored. No refresh token rotation. If token expires, user must re-login completely.

5. **localStorage Clear on 401**: The 401 interceptor in api.js clears localStorage even if it's a transient auth failure. Consider retry logic before clearing.

## Compliance

- ✅ Did not touch `resources/js/pages/OperationsDashboard.jsx`
- ✅ Did not modify Laravel routes/controllers
- ✅ Did not modify backend API contracts
- ✅ Did not modify tests
- ✅ Used existing Vietnamese text patterns
- ✅ Kept login form compact (no added steps)