# php-hello-world-app

Minimal PHP + PDO (PostgreSQL) app with an idempotent migration script — baseline plain-PHP recipe on Zerops.

## Zerops service facts

- HTTP port: `80`
- Siblings: `db` (PostgreSQL) — env: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Runtime base: `php-apache@8.5` (build on `php@8.5`)

## Zerops dev

Runtime (`php-apache`) serves file changes immediately — edit PHP under `/var/www` and it takes effect on the next request. No dev server to start.

**All platform operations (deploy, env / scaling / storage / domains) go through the Zerops development workflow via `zcp` MCP tools. Don't shell out to `zcli`.**

## Notes

- No `start:` command — the `php-apache` base image runs PHP-FPM in foreground by default.
- `zsc execOnce ${appVersionId}` gates `migrate.php` to exactly one container per deploy.
