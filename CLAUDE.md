# CLAUDE.md — Damascus Parking (SCP-Syria)

## Working Rules

### Mandatory: Branch Before Every Change

**This is a hard rule. Never modify any file without first creating a git branch and committing the current state.**

1. Create a branch named after the feature or fix being worked on
2. Stage and commit every file that will be touched — this is the rollback point
3. Do **not** push to remote
4. Then make the changes on that same branch

```bash
git checkout -b feature/my-feature-name
git add <files being changed>
git commit -m "backup: before <description of change>"
# now make the changes
```

To revert a file: `git checkout <branch>^ -- path/to/file`
To revert everything: `git checkout <branch>^`

---

## Project Overview

**Damascus Parking** (دمشق باركينغ) is a full-stack web application for parking lot management in Damascus, Syria. It allows the public to search and book parking spaces, operators to manage vehicle check-ins/check-outs, and admins to oversee all operations.

- **Framework:** Laravel 12.0 (PHP ^8.2)
- **Database:** SQLite (`database/database.sqlite`)
- **Auth:** Laravel Sanctum 4.0 (session-based for web, token-based for API)
- **Frontend:** Vite 7 + Bootstrap 5.3 + Sass + Leaflet maps
- **Language/Layout:** Arabic (RTL throughout), font: Cairo (Google Fonts)
- **Timezone:** Asia/Damascus
- **Local URL:** http://localhost:8000

---

## Development Setup

```bash
# Full install + migrate + build
composer setup

# Start all dev services (server, queue, logs, Vite HMR)
composer dev

# OR individually:
php artisan serve
npm run dev

# Run tests
composer test
# or: php artisan config:clear && php artisan test
```

**Vite entry points:** `resources/css/app.scss`, `resources/js/app.js`

---

## Architecture

### Directory Structure

```
app/
  Http/
    Controllers/
      Admin/          # Admin-only: Dashboard, ParkingLot, Booking
      Api/            # REST API: ParkingLot, Booking, CarRegistry
      Operator/       # Operator-only: OperatorController
      ParkingController.php  # Public landing page
    Middleware/
      EnsureAdmin.php
      EnsureOperator.php
    Requests/         # Form validation: Booking, CheckIn, ParkingLot
    Resources/        # API response transformers
  Models/
    User.php          # Roles: admin | operator | user
    ParkingLot.php    # Scopes: active(), withStatus(); computed attributes
    Booking.php       # Status enum: active | completed | cancelled
    CarRegistry.php   # Scope: active() (exit_time IS NULL)
routes/
  web.php             # Pages + auth
  api.php             # /api/v1/* REST endpoints
  auth.php            # Login/register/logout
resources/
  views/
    admin/            # Admin Blade templates
    operator/         # Operator Blade templates
    auth/             # Login & register pages
    layouts/          # Base layouts
    index.blade.php   # Public landing page
  css/app.scss        # ~780 lines of custom RTL-aware styles
  js/app.js           # Bootstrap + Axios setup
```

### Roles & Access

| Role       | Middleware      | Access                                      |
|------------|-----------------|---------------------------------------------|
| `admin`    | `EnsureAdmin`   | Full admin dashboard, parking lot CRUD, all bookings |
| `operator` | `EnsureOperator`| Vehicle check-in/check-out, active bookings for assigned lot |
| `user`     | (auth only)     | Public search, create bookings via API      |

Both middleware classes return Arabic 403 messages on unauthorized access.

---

## Database Schema

### `users`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | bcrypt |
| role | string | 'admin' \| 'operator' \| 'user' (default) |

### `parking_lots`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| address | string | |
| total_capacity | integer | |
| price_per_hour | decimal | |
| latitude / longitude | decimal | Used by Leaflet map |
| working_hours | string | Default '24/7' |
| is_active | boolean | Toggleable by admin |

Computed attributes (not stored): `available_spaces`, `occupied_spaces`, `usage_percentage`

### `bookings`
| Column | Type | Notes |
|--------|------|-------|
| parking_lot_id | FK | |
| customer_name | string | |
| phone | string | Format: `09xxxxxxxx` |
| start_time / end_time | datetime | |
| status | enum | 'active' \| 'completed' \| 'cancelled' |

### `car_registries`
| Column | Type | Notes |
|--------|------|-------|
| parking_lot_id | FK | |
| plate_number | string | |
| entry_time | datetime | |
| exit_time | datetime nullable | NULL = currently parked |

---

## API Reference (`/api/v1`)

All responses follow: `{ "success": bool, "data": ..., "message": "..." }`

### Parking Lots
```
GET  /api/v1/parking-lots              # Paginated list with occupancy status
GET  /api/v1/parking-lots/{id}         # Single lot details
GET  /api/v1/parking-lots/search?q=   # Search by name/address
GET  /api/v1/parking-lots/{id}/status  # Real-time occupancy
```

### Bookings
```
POST /api/v1/bookings                  # Create booking
GET  /api/v1/bookings                  # Recent bookings (paginated)
```
Required fields: `parking_lot_id`, `customer_name`, `phone` (09xxxxxxxx), `start_time`, `end_time`

### Car Registry (Check-in/Check-out)
```
POST /api/v1/car-registries            # Register car entry
PUT  /api/v1/car-registries/{id}/exit  # Register car exit
```
Check-in requires: `parking_lot_id`, `vehicle_plate`, `duration_hours` (0.25–24)

---

## Web Routes Summary

| Path | Middleware | Description |
|------|-----------|-------------|
| `GET /` | — | Public landing page |
| `GET /login` | guest | Login form |
| `GET /register` | guest | Register form |
| `POST /logout` | auth | Logout |
| `GET /admin/dashboard` | admin | Admin stats & charts |
| `GET /admin/parking-lots` | admin | List/manage parking lots |
| `POST /admin/parking-lots` | admin | Create parking lot |
| `PUT /admin/parking-lots/{id}` | admin | Update parking lot |
| `POST /admin/parking-lots/{id}/toggle` | admin | Toggle active status |
| `GET /admin/bookings/active` | admin | Active bookings |
| `POST /admin/bookings/{id}/complete` | admin | Mark booking complete |
| `GET /operator/dashboard` | operator | Operator view |
| `POST /operator/check-in` | operator | Vehicle entry |
| `POST /operator/{booking}/checkout` | operator | Vehicle exit + fee |
| `GET /admin/stats` | admin | JSON stats (AJAX) |
| `GET /admin/charts` | admin | JSON chart data (AJAX) |

---

## Validation Rules

**Phone:** must match `^09[0-9]{8}$` (Syrian mobile format)

**Booking:**
- `start_time`: required, after:now
- `end_time`: required, after:start_time
- Custom: parking lot must have available capacity

**Parking Lot:**
- `total_capacity`: integer, 1–10000
- `price_per_hour`: numeric, 0.01–1000
- `latitude`: -90 to 90, `longitude`: -180 to 180

**Operator Check-in:**
- `duration_hours`: numeric, 0.25–24

---

## Frontend & Styling

**Color palette (CSS variables in `app.scss`):**
- Primary: `#6366f1` (indigo)
- Sidebar bg: `#0f172a`
- Muted text: `#64748b`
- Page bg: `#f1f5f9`

**RTL-specific patterns:**
- `direction: rtl` on `<html>`
- Input groups reverse border-radius via CSS overrides in `[dir="rtl"]` block in `app.scss`
- Flexbox rows appear mirrored naturally; no special JS needed
- Margin/padding use `inset-inline-*` where needed

**Bootstrap RTL spacing — critical rule:**
Bootstrap is imported as SCSS (`@import "bootstrap/scss/bootstrap"`), which compiles `me-*` to physical `margin-right` and `ms-*` to `margin-left`. In RTL, icons sit on the **right** and text on the **left**, so `margin-right` on an icon pushes *away* from the text — the gap appears on the wrong side.

**The fix** is already applied in the `[dir="rtl"]` block at the bottom of `app.scss`: it swaps all `me-*`/`ms-*` physical margins so they behave like Bootstrap's logical properties.

**Rule: never add `me-*` to fix icon-to-text spacing — it already works correctly via the global `[dir="rtl"]` override. Do not remove or change those overrides.** If a new icon appears stuck to its label, the cause is always the same: Bootstrap's LTR SCSS compilation. The fix is already in place globally — just use the standard Bootstrap spacing classes (`me-1`, `me-2`, etc.) and they will work correctly in RTL.

**Responsive breakpoints:**
- `<768px`: sidebar hidden, bottom nav visible (56px)
- `768px–992px`: sidebar can toggle
- `>992px`: full sidebar + content layout

**Libraries:**
- Leaflet 1.9.4 — interactive parking lot map
- Bootstrap Icons 1.11.3 — icon set
- Axios 1.11.0 — AJAX calls from admin/operator JS

---

## Fee Calculation Logic

```php
$duration = ceil(now()->diffInHours($entry_time));   // rounds up
$fee = $duration * $parking_lot->price_per_hour;
```

Fees are always rounded up to the nearest hour.

---

## Availability Calculation

Available spaces = `total_capacity` − (active bookings count + active car registries count)

`CarRegistry::active()` scope: `whereNull('exit_time')` (or `exit_time > now()`)
`Booking::active()` scope: `where('status', 'active')`

Overbooking is prevented at API validation time in `StoreBookingRequest`.

---

## Key Artisan Commands

```bash
php artisan migrate                 # Run migrations
php artisan migrate:fresh --seed    # Reset DB and seed
php artisan tinker                  # REPL for debugging
php artisan route:list              # List all routes
php artisan config:clear            # Clear config cache
php artisan make:model Foo -mcr     # Model + migration + controller + resource
```

---

## Testing

Tests live in `tests/Feature/` and `tests/Unit/`. PHPUnit 11 is configured in `phpunit.xml`. The test database uses the SQLite in-memory driver by default.

```bash
composer test
# or
php artisan config:clear && php artisan test
```

---

## User-Facing Pages (authenticated)

| Route | Name | Description |
|-------|------|-------------|
| `GET /profile` | `profile.show` | User info + edit name + change password |
| `PATCH /profile` | `profile.update` | Update name (error bag: `updateName`) |
| `PATCH /profile/password` | `profile.password` | Change password (error bag: `updatePassword`) |
| `GET /dashboard` | `user.dashboard` | User's booking history + stats |

**Profile dropdown** is a Blade partial at `resources/views/partials/user-dropdown.blade.php`. It is included in both `layouts/user.blade.php` and `index.blade.php`. When the user is a guest it shows the login button; when authenticated it shows an avatar circle with a dropdown containing profile, reservations, operator/admin links (role-gated), and sign-out.

**Post-login redirect** (in `routes/auth.php`) is role-based:
- `admin` → `/admin/dashboard`
- `operator` → `/operator/dashboard`
- `user` → `/dashboard`

**`bookings.user_id`** — nullable FK added via migration `2026_04_15_072226`. Existing seeded bookings have `user_id = NULL`. New bookings made by a logged-in user should set `user_id = Auth::id()`.

---

## Notes & Known Patterns

- Admin stats (`/admin/stats`, `/admin/charts`) are fetched via AJAX on page load — not cached, computed fresh each request.
- The `CarRegistry` model tracks physical vehicle presence; `Booking` tracks reservations — they are separate but both count toward capacity.
- `StoreParkingLotRequest` and `StoreBookingRequest` are in `app/Http/Requests/`.
- Arabic error messages are returned by both middleware and validation responses.
- The `TODO.md` at the root is nearly empty — "Update with checked" is the only entry.
- Some API routes have commented-out `auth:sanctum` middleware; add it back before deploying to production.
