# PHP Hello World Recipe App

<!-- #ZEROPS_EXTRACT_START:intro# -->
A minimal [PHP](https://www.php.net/) application that connects to [PostgreSQL](https://www.zerops.io) and exposes a single endpoint demonstrating database connectivity and idempotent migrations on [Zerops](https://zerops.io).
Used within [PHP Hello World recipe](https://app.zerops.io/recipes/php-hello-world) for [Zerops](https://zerops.io) platform.
<!-- #ZEROPS_EXTRACT_END:intro# -->

⬇️ **Full recipe page and deploy with one-click**

[![Deploy on Zerops](https://github.com/zeropsio/recipe-shared-assets/blob/main/deploy-button/light/deploy-button.svg)](https://app.zerops.io/recipes/php-hello-world?environment=small-production)

![php cover](https://github.com/zeropsio/recipe-shared-assets/blob/main/covers/svg/cover-php.svg)

## Integration Guide

<!-- #ZEROPS_EXTRACT_START:integration-guide# -->

### 1. Adding `zerops.yaml`

The main application configuration file you place at the root of your repository.
It tells Zerops how to build, deploy, and run your application.

```yaml
zerops:
  # Production setup — optimized Composer install, minimal deploy footprint.
  - setup: prod
    build:
      base: php@8.5
      buildCommands:
        # Install production dependencies only; --no-dev excludes test tools,
        # --optimize-autoloader builds a classmap for faster class resolution.
        - composer install --no-dev --optimize-autoloader
      deployFiles:
        - ./index.php
        - ./migrate.php
        # vendor/ holds the Composer autoloader (and any packages you add).
        - ./vendor
      # Cache vendor/ between builds — Composer restores unchanged packages
      # from cache, skipping redundant network fetches on every deploy.
      cache:
        - vendor

    # Readiness check: new containers must answer HTTP 200 on port 80
    # before the project balancer routes traffic to them. This is what
    # enables zero-downtime deploys (temporaryShutdown: false by default).
    deploy:
      readinessCheck:
        httpGet:
          port: 80
          path: /

    run:
      base: php-apache@8.5
      # PHP-FPM starts via the php-apache base image default (foreground mode).
      # Apache runs alongside it as an OS-level service.
      # No 'start' needed here — the base image default handles it.
      # Run migration exactly once per deploy, regardless of container count.
      # initCommands run per container before traffic is accepted; zsc execOnce
      # ensures one container executes the migration and all others wait.
      # --retryUntilSuccessful handles brief DB startup delays on first deploy.
      initCommands:
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php migrate.php
      envVariables:
        # DB_NAME matches the PostgreSQL service hostname — a static value,
        # not a generated variable (Zerops names the database after hostname).
        DB_NAME: db
        # The remaining vars reference generated credentials from the 'db'
        # service. Pattern: ${hostname_key} → e.g., ${db_hostname}, ${db_port}.
        DB_HOST: ${db_hostname}
        DB_PORT: ${db_port}
        DB_USER: ${db_user}
        DB_PASS: ${db_password}

  # Dev setup — deploys full source for live development via SSH.
  # PHP is interpreted per-request: edit files in /var/www and changes
  # take effect immediately — no rebuild or container restart required.
  - setup: dev
    build:
      base: php@8.5
      buildCommands:
        # Install all dependencies including dev packages, so the developer
        # has testing and debugging tools available after SSH.
        - composer install
      deployFiles:
        # Deploy the entire working directory — source files, vendor/,
        # and zerops.yaml so 'zcli push' works from the dev container.
        - ./
      cache:
        - vendor

    run:
      base: php-apache@8.5
      initCommands:
        # Migration runs once per deploy — DB is ready when SSH session starts.
        - zsc execOnce ${appVersionId} --retryUntilSuccessful -- php migrate.php
      envVariables:
        DB_NAME: db
        DB_HOST: ${db_hostname}
        DB_PORT: ${db_port}
        DB_USER: ${db_user}
        DB_PASS: ${db_password}
      # PHP-FPM is the Zerops-managed process for php-apache services —
      # omitting 'start' uses the base image default, which runs PHP-FPM
      # in foreground mode. Apache runs alongside it as an OS service.
      # SSH in and edit PHP files in /var/www; changes take effect on the
      # next request without any restart.
```

<!-- #ZEROPS_EXTRACT_END:integration-guide# -->
