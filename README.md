# Property Management

A record-keeping application for Bangladeshi land deeds — dag (দাগ), khatian (খতিয়ান),
mouja (মৌজা) and dolil (দলিল) — with search, document attachments, and an
inheritance-share calculator. English and Bangla throughout.

Built as a small custom MVC application: no framework, no Composer, no build
step. Drop it in `htdocs`, import the schema, and it runs.

---

## Requirements

- PHP 8.0+ with `pdo_mysql`, `fileinfo` and `mbstring`
- MySQL 5.7+ / MariaDB 10.2+
- Apache with `mod_rewrite` (XAMPP is fine)

## Installation

```bash
# 1. Create the schema and seed the first administrator
mysql -u root < database/schema.sql
mysql -u root property_v2 < database/seed.sql

# 2. Point a browser at the project
#    http://localhost/property-management/
```

There are two sign-in pages, and each links to the other by name:

| Page | Who it is for |
|---|---|
| `/login` | Customers — managing their own property records |
| `/admin/login` | Staff and administrators. Customer accounts are refused here |

Sign in at **`/admin/login`** with `admin@example.com` / `ChangeMe!2026`.
A password change is forced on first sign-in, and you should change the address
to one you control — password reset has nowhere to go otherwise.

### If you are locked out

`seed.sql` will not overwrite an existing administrator's password, so re-running
it does not help. Set a new one directly:

```bash
# generate a hash for whatever password you want
php -r "echo password_hash('YourNewPassword', PASSWORD_BCRYPT), PHP_EOL;"

# then, with the hash it printed:
mysql -u root property_v2 -e "UPDATE users SET password='<paste-hash>', must_change_password=1 WHERE email='admin@example.com';"
```

Never store a plaintext password in the database — the `password` column holds a
bcrypt hash and `password_verify()` will simply fail against anything else.

To use different database credentials without touching version control, create
`includes/config.local.php`:

```php
<?php
cfg('DB_USER', 'property_app');
cfg('DB_PASS', 'your-password');
cfg('APP_ENV', 'prod');   // hides errors, logs them instead
```

It is loaded before the defaults and is gitignored.

---

## Layout

```
.htaccess          front controller rewrite + hardening
index.php          the only web-reachable PHP file
includes/          all classes, config and shared code (deny-all .htaccess)
  lang/            en.php · bn.php
views/             templates only — no SQL, no literal English
resources/         css/ js/ vendor/{bootstrap, bootstrap-icons, noto-bengali}
storage/           uploads/ and logs/ (deny-all .htaccess, PHP engine off)
database/          schema.sql · seed.sql · reindex.php
```

Classes are flat in `includes/` as `class.Name.php` under the `App\` namespace,
autoloaded by `includes/bootstrap.php`. Adding a class means creating the file —
there is nothing to register.

**Routing.** Every request that is not a real file goes to `index.php`, which
dispatches through `includes/routes.php`. No URL carries a `.php` extension.
Record ids are path segments (`/properties/12/edit`); filters travel as one
encoded `?q=` token built by `url()`.

> The `?q=` token is **presentation, not security**. It is base64url and anyone
> can decode it, and because it expands into `$_GET` a visitor can craft any
> values they like. Ownership checks, CSRF tokens, bound parameters and
> `ORDER BY` whitelisting are what actually protect the app.

**Adding a page.** Write a controller method, add a line to `routes.php` naming
its access rule, and add the view. Access rules (`guest`, `auth`, `customer`,
`staff`, `admin`) live in `includes/class.Middleware.php` — never inside a
controller.

---

## Roles and plans

Two independent axes:

- **Role** (`users.role`) decides what you may do: `customer`, `staff`, `admin`.
  The role → permission map is a constant in `includes/class.Permission.php`;
  checks read `Auth::can('user.delete')`.
- **Plan** (`users.plan_code` → `plans`) decides how much: record limit, whether
  document upload and CSV export are included.

Plan limits are **data, not code** — edit them at `/admin/plans`. A blank limit
means unlimited, and `users.property_limit_override` raises the ceiling for one
account without inventing a new plan.

Going over a limit makes an account **read-only, not broken**: creating new
records is blocked while viewing, editing and deleting still work, so the
account can be brought back under the limit.

---

## Search

Dag and khatian numbers are entered as comma-separated lists and stored exactly
as typed. `property_identifiers` holds the same values split into rows, and
search matches whole tokens against that index.

That table is **derived and rebuildable**:

```bash
php database/reindex.php
```

The raw strings are canonical, so a bug in the splitter can be fixed and the
index regenerated without any user data having been lost.

Filters combine with AND by default (choose "any filter" to widen), dag and
khatian match current *or* previous unless narrowed, and owner search matches
by prefix (a slower "contains" mode is offered explicitly).

---

## Localization

`includes/lang/en.php` and `bn.php`. Views call `t('key')` or `te('key')` (the
escaping form) and contain no literal English; a missing Bangla key falls back
to English rather than rendering blank.

Noto Sans Bengali is vendored in `resources/vendor/noto-bengali/` — Bootstrap's
default font stack renders Bengali as empty boxes on Windows without it.

Record **values** — deed, dag and khatian numbers — always display in Latin
digits, because they must match the paper document exactly.

---

## Development notes

- Set `APP_ENV` to `dev` for on-screen errors, `prod` to log them instead.
- Outgoing mail defaults to a log driver, writing to `storage/logs/mail.log`.
  XAMPP has no working mail path, so a real `mail()` call would fail silently
  and password reset would appear to work while sending nothing. Set
  `cfg('MAIL_DRIVER', 'mail')` once a mail path exists.
- Uploads are validated by content (`finfo`), stored under a random name with a
  `.bin` extension, and served only through `/documents/{id}` after an ownership
  check. `gd` and `intl` are not required.
- Every state-changing route is POST and CSRF-verified centrally in `index.php`;
  no controller can forget.

## Security

Properties this codebase maintains, and which are worth preserving in any change
you make to it:

- every query is a prepared statement with bound parameters;
- every value rendered into a page goes through `e()`;
- every state-changing route is POST and CSRF-verified;
- sessions are regenerated on sign-in and destroyed on sign-out;
- sign-in attempts are throttled per identifier;
- database credentials live in one file, and the application never issues DDL.

Before putting this on a network:

1. Change the seeded administrator's e-mail and password.
2. Create a dedicated MySQL user with `SELECT, INSERT, UPDATE, DELETE` on
   `property_v2` only — the application never issues DDL — and put it in
   `includes/config.local.php`.
3. Set `APP_ENV` to `prod`.
4. Serve over HTTPS; the session cookie then sets `Secure` automatically.
