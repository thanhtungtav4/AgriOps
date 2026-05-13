# T1.1 Laravel Foundation - Learnings

## Completed: Laravel Project Created

### Key Pattern
When `composer create-project` fails due to non-empty directory (hidden files like .docs, .sisyphus):
1. Move hidden directories to /tmp/
2. Run composer create-project
3. Restore hidden directories

### Command Used
```bash
mv .docs .sisyphus /tmp/ && composer create-project laravel/laravel . && mv /tmp/.docs /tmp/.sisyphus .
```

### Verification Results
- `php artisan --version`: Laravel Framework 13.8.0
- `php artisan route:list`: 4 default routes working
- All 109 dependencies installed
- Migrations ran successfully (users, cache, jobs tables)
- Application key generated

### Notes
- Laravel v13.8.0 installed (latest stable)
- SQLite used for development database (migrations auto-ran)
- .docs and .sisyphus hidden directories preserved
- No additional packages installed (plain Laravel only as required)
