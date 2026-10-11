# Deploy on Render — start here

This package uses HTML, CSS, JavaScript, PHP and **MySQL**. No XAMPP is needed.

## 1. Get a MySQL database

Create a MySQL database with a hosting provider and keep its host, port, database name, username and password. It must accept connections from your Render service. A localhost database on your laptop is not reachable from Render.

Render's free web service does not include MySQL. This package connects to a separately hosted MySQL database; it does not create one. Hosting MySQL on Render itself requires a paid private service with persistent storage. Do not run MySQL inside this free web container: its data would be lost on restarts.

## 2. Upload to GitHub

Extract the ZIP. Create a GitHub repository and upload the **contents** of `online-polling-voting-system-render`, so `render.yaml` and `Dockerfile.render` are at the repository root.

## 3. Deploy

In Render, choose **New → Blueprint**, connect the GitHub repository and enter the requested environment values:

| Variable | Value |
| --- | --- |
| DB_HOST | Your MySQL provider's hostname |
| DB_PORT | Your provider's port, normally 3306 |
| DB_NAME | An existing empty database name |
| DB_USER | Database username with table creation/read/write permissions |
| DB_PASSWORD | Database password |
| ADMIN_EMAIL | Your chosen admin login email |
| ADMIN_PASSWORD | A unique 12–72 character password |

Apply the Blueprint. PHP runs under Apache and listens on Render's PORT. Startup creates the database tables and initial admin account. It does not erase existing records. Open the URL Render provides once deployment is live.

Alternatively, use **New → Web Service**, connect the repository, select **Docker**, choose **Free**, set **Dockerfile Path** to `./Dockerfile.render`, and add the variables above. Leave Docker Command blank. Set the health check path to `/index.php`.

## 4. Use your website

Sign in with ADMIN_EMAIL and ADMIN_PASSWORD. Open **Manage polls → Create poll**. Other users register through **Join community**. No example polls or votes are added automatically.

After successful setup, remove ADMIN_PASSWORD from Render's environment settings. The existing admin account remains in MySQL on later deploys. Changing the environment variable does not reset an existing account's password.

## Database TLS

The package supports an optional `DB_SSL_CA` environment variable. If your provider requires a custom CA certificate, add that public CA certificate as a Render secret file and set DB_SSL_CA to its absolute path, such as `/etc/secrets/mysql-ca.pem`. For a publicly trusted database certificate, set DB_SSL_CA to `/etc/ssl/certs/ca-certificates.crt`. Use the exact hostname on your provider's certificate. When set, certificate verification is enabled. Obtain the CA and connection requirements from your MySQL provider.

## Troubleshooting

- Database connection failure: check host, port, credentials, TLS and your database provider's firewall settings.
- Unknown database: create DB_NAME at your MySQL provider before deploying.
- Admin setup error: check the email and password length; use a fresh database for first installation.
- No polls on the homepage: sign in as admin and create a poll.
- First visit is slow: the free service may need to wake up.

The Codespaces configuration remains included, using the original Dockerfile. Render must use **Dockerfile.render**. Sessions are local to the web instance, so users may need to sign in again after a restart. Votes and accounts remain in the external MySQL database.

Verification: JavaScript syntax, shell syntax, JSON/YAML and ZIP structure were checked. This creation environment could not run PHP, MySQL, Docker or a live Render deployment. Run the included acceptance checklist after deployment.
