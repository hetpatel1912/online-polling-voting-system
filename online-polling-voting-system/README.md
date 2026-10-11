# CampusVote — Online Polling and Voting System

A responsive college project using **HTML, CSS, JavaScript, PHP and MySQL**. PHP handles every registration, login, poll change and vote; MySQL stores the data. No XAMPP is required. There are no frontend frameworks or paid APIs.

## Run in GitHub Codespaces

1. Extract this ZIP. Upload the **contents** of `online-polling-voting-system` into your GitHub repository. The root must contain `Dockerfile`, `compose.yaml`, `public/`, and the hidden `.devcontainer/` folder. If uploading through the browser does not include hidden folders, create `.devcontainer/devcontainer.json` manually using the included file's contents.
2. Open your repository → **Code → Codespaces → Create codespace on main**.
3. Wait for the PHP container and MySQL service to start. Database tables are installed automatically. If you already had a Codespace, run **Codespaces: Rebuild Container** from the Command Palette.
4. In the Codespace terminal run:

   ```sh
   php scripts/create-admin.php
   ```

   Enter your own name, email and password. There is no default admin password. The script creates a new account and does not overwrite an existing account.

5. Optional: add six example polls with real, initially empty vote counts:

   ```sh
   php scripts/seed.php
   ```

6. Open the **Ports** tab, find **8000**, and choose **Open in Browser**. Sign in with your admin credentials. Use **Manage polls → Create poll**. Other accounts can register using **Join community**.

The PHP web server starts automatically; you do not need to run another server command. If the first page says the database is not ready, wait for setup to finish and refresh. You can rerun `php scripts/install.php` safely. MySQL runs in a separate container and is not exposed on a public port.

The forwarded website is private by default. Keep it private for development. Codespaces is a development environment, not an always-on public hosting service. Its URL only works while the Codespace is running and access depends on port visibility. MySQL data persists in the Docker volume across normal container restarts; deleting the Codespace or the volume deletes its data. Export important records before deleting it.

## What is included

- Responsive green-and-cream dashboard, mobile navigation, search and status filters.
- Registration, login, logout, hashed passwords and server-side sessions.
- Admin-only poll creation, editing before the first vote, and early closing.
- Active, upcoming and closed polls with deadlines in India Standard Time.
- One vote per registered account per poll, enforced by a MySQL unique constraint.
- Transactional voting and option-to-poll foreign keys.
- Results with accessible bar charts after closing; no early result breakdown.
- Personal participation history and member enable/disable controls.
- Prepared SQL statements, escaped output, CSRF tokens and authentication throttling.
- Optional sample polls with no fabricated votes.

## Project structure

```text
.devcontainer/devcontainer.json  Codespaces configuration
compose.yaml                    PHP and MySQL services
Dockerfile                      PHP with PDO MySQL extension
public/index.php                Pages and HTML templates
public/assets/style.css         Responsive interface
public/assets/app.js             Search, filters and form enhancements
app/bootstrap.php               Database, sessions and helpers
app/actions.php                 Server-side actions and validation
app/config.example.php          Environment-based database configuration
database/schema.sql             MySQL table definitions
scripts/install.php             Idempotent table installer
scripts/create-admin.php        Interactive admin setup
scripts/seed.php                Optional example polls
tests/acceptance.md              Functional test checklist
```

## Run with Docker outside Codespaces

With Docker and Compose installed:

```sh
docker compose up -d --build
docker compose exec web php scripts/install.php
docker compose exec web php scripts/create-admin.php
docker compose exec web php scripts/seed.php
```

Open `http://localhost:8000`. To stop services without removing data, use `docker compose stop`. Do not run `docker compose down -v` unless you intend to erase the database.

## Run on another PHP + MySQL environment

Requires PHP 8.1+ with `pdo_mysql` and MySQL 8+ (InnoDB). Create a database named `campus_vote`; copy `app/config.example.php` to `app/config.php` and enter your connection settings. Run the three setup scripts above, then `php -S localhost:8000 -t public`. The web document root must be **public/** so configuration, scripts, and SQL files cannot be downloaded.

## Data model and project flow

`users` stores accounts and roles; `polls` stores questions and deadlines; `poll_options` stores choices; `votes` connects each account and poll to one option; `login_limits` stores temporary authentication attempt counters.

Browser form → PHP validation and access checks → prepared MySQL statement → redirect → updated HTML page. JavaScript adds filtering and confirmations, but vote validation always runs in PHP. The database prevents duplicate votes even when requests race.

## Scope and deployment notes

This is an educational community-polling application. One **account** gets one vote; it does not verify real-world identity or prevent a person from registering multiple emails. Votes are linked to accounts in MySQL, so they are not cryptographically anonymous. Aggregate results are visible to visitors after closing. Do not use this prototype for statutory elections or secret ballots.

The Compose fallback passwords are for a private development environment. For public hosting, use unique database credentials, HTTPS, a production PHP server such as PHP-FPM or Apache, backups, and restrict database access. Configure the web root as `public/`. An always-on PHP/MySQL host is separate from the included Codespaces setup.

## Verification status

JavaScript syntax and package structure were checked during creation. This creation environment did not provide PHP, MySQL or Docker, and installing them was blocked, so PHP execution and full database integration have **not** been verified here. Run `tests/acceptance.md` in Codespaces before submitting or deploying.
