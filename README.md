# nxu.hu
This is the source code of nxu.hu, built with Astro.

## Local development
The toolchain (bun) comes from the Nix flake, nothing needs to be installed globally.

```sh
direnv allow            # or: nix develop
cp .env.example .env    # set PUBLIC_CUBE_API_URL
bun install
bun run dev
```

## Deployment
Every push to `master` runs `.github/workflows/deploy.yml`: it builds the static site inside the Nix dev shell and
rsyncs `dist/` to the server over SSH, where Caddy serves it.

Repository settings needed by the workflow:

| Kind     | Name                  | Value                                             |
|----------|-----------------------|---------------------------------------------------|
| Secret   | `DEPLOY_SSH_KEY`      | Private key of the deploy user                    |
| Secret   | `DEPLOY_KNOWN_HOSTS`  | Output of `ssh-keyscan <host>`                    |
| Secret   | `DEPLOY_HOST`         | Server hostname                                   |
| Secret   | `DEPLOY_USER`         | Deploy user (must be able to write `DEPLOY_PATH`) |
| Variable | `DEPLOY_PATH`         | e.g. `/var/www/nxu.hu`                            |
| Variable | `PUBLIC_CUBE_API_URL` | URL of the cubing stats API                       |

The server needs `rsync` installed and a Caddy site like this:

```caddyfile
nxu.hu, www.nxu.hu {
    root * /var/www/nxu.hu
    encode zstd gzip
    try_files {path} {path}.html {path}/index.html
    file_server

    @assets path /_astro/* /fonts/*
    header @assets Cache-Control "public, max-age=31536000, immutable"

    handle_errors {
        @404 expression {err.status_code} == 404
        rewrite @404 /404.html
        file_server
    }
}
```
