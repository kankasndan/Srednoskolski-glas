# Railway deploy (test host)

One Git repo, **two services**. Cookie login only works if you follow the env names below.

## 1. Push this repo to GitHub

Railway deploys from GitHub. The root directory of each service is a subfolder.

## 2. Create a Railway project

1. [railway.app](https://railway.app) → New project → Empty project.
2. **New service → GitHub repo** → this repo.
   - Settings → **Root Directory:** `/backend`
   - Rename the service to `backend`
3. **New service → GitHub repo** → same repo.
   - Settings → **Root Directory:** `/frontend`
   - Rename the service to `frontend`
4. On **backend**: New → **Database → MySQL**. Wait until it is running.

Generate an app key on your machine (from `backend/`):

```bash
php artisan key:generate --show
```

## 3. Backend variables

Open the `backend` service → Variables. Paste from `backend/.env.railway.example`, then fill secrets.

Required:

| Variable | Value |
|---|---|
| `APP_KEY` | output of `key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://${{RAILWAY_PUBLIC_DOMAIN}}` |
| `FRONTEND_URL` | `https://${{frontend.RAILWAY_PUBLIC_DOMAIN}}` |
| `SANCTUM_STATEFUL_DOMAINS` | `${{frontend.RAILWAY_PUBLIC_DOMAIN}}` (no `https://`) |
| `SESSION_SAME_SITE` | `none` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_DOMAIN` | leave empty |
| `SESSION_ENCRYPT` | `true` |
| `DB_CONNECTION` | `mysql` |
| `DB_URL` | `${{MySQL.MYSQL_URL}}` |
| `CONTENT_MODERATION_ON_FAILURE` | `reject` |

Also set ImageKit, `GEMINI_API_KEY`, and `GIPHY_API_KEY` the same as local.

Optional staff login: set `ADMIN_SEED_PASSWORD` to a strong password. That creates `admin@srednoskolskiglas.mk` / `moderator@srednoskolskiglas.mk` on boot (see `RailwaySeeder`).

Generate a public URL: backend service → Settings → Networking → **Generate domain**.

## 4. Frontend variables

Frontend service → Variables:

| Variable | Value |
|---|---|
| `NEXT_PUBLIC_API_URL` | `https://${{backend.RAILWAY_PUBLIC_DOMAIN}}` |

Generate a public URL for `frontend` too.

`NEXT_PUBLIC_API_URL` is compiled into the JS bundle. If the backend domain was not ready at first build, **Redeploy** the frontend after it exists.

Then redeploy **backend** once so `FRONTEND_URL` / Sanctum see the frontend host.

## 5. Order of first deploy

1. Deploy **backend** (with MySQL + `APP_KEY`).
2. Generate frontend domain, set `NEXT_PUBLIC_API_URL`, deploy **frontend**.
3. Redeploy **backend** (CORS + Sanctum now have the real frontend host).

Health checks: backend `/up`, frontend `/`.

## 6. Custom domains later

When you have `app.example.com` and `api.example.com`:

- Point both DNS CNAMEs at Railway.
- Set `SESSION_DOMAIN=.example.com` and `SESSION_SAME_SITE=lax`.
- Put those hosts in `FRONTEND_URL`, `APP_URL`, `SANCTUM_STATEFUL_DOMAINS`, `NEXT_PUBLIC_API_URL`.
- Redeploy both.

Do not set `SESSION_DOMAIN=.railway.app` — that suffix is public and browsers will drop the cookie.
