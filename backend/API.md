# API reference (for frontend)

Base URL (local): `http://localhost:8000`

All routes below are under `/api` unless noted.

Use `credentials: "include"` on every request (session cookie auth). For `POST` / `PUT` / `DELETE`, first call `GET /sanctum/csrf-cookie`, then send header `X-XSRF-TOKEN` from the `XSRF-TOKEN` cookie. See `frontend/src/lib/api.js`.

---

## Conventions

### Success shapes

Most newer endpoints wrap payloads in `data`:

```json
{
  "data": { }
}
```

Paginated lists also include `meta` and `links`:

```json
{
  "data": [ ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 5,
    "total": 12
  },
  "links": {
    "first": "http://localhost:8000/api/p/drzhavna_matura/threads?page=1",
    "last": "http://localhost:8000/api/p/drzhavna_matura/threads?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/p/drzhavna_matura/threads?page=2"
  }
}
```

> Some older auth/onboarding/media endpoints still return top-level keys (`user`, `cities`, `message`) instead of `data`. Those are documented as they actually respond today.

### Errors

```json
{
  "message": "Human-readable summary."
}
```

Validation (`422`):

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

| Status | When |
|--------|------|
| `401` | Not logged in (`Unauthenticated.`) |
| `404` | Missing forum/thread/resource |
| `422` | Validation failed |
| `500` | Server error |

---

## Shared object shapes

These appear inside forum/thread/comment responses.

### Author (`UserResource`)

Never includes email/password.

```json
{
  "id": 1,
  "username": "ana_mk",
  "imageUrl": "https://…/avatar.png",
  "school": {
    "id": 12,
    "name": "СУГС Јосип Броз Тито",
    "city": "Скопје"
  }
}
```

`school` is `null` if the user has no student data.

### Forum card (sidebar)

```json
{
  "id": 3,
  "name": "Државна матура",
  "slug": "drzhavna_matura",
  "type": "general",
  "school_id": null,
  "imageUrl": "https://…/forum.png",
  "threads_count": 42,
  "members_count": 120
}
```

### Forum full (forum page)

Same as card, plus:

```json
{
  "description": "Дискусии за државна матура…",
  "bannerUrl": "https://…/banner.png",
  "school": null,
  "is_following": false
}
```

`is_following` is included only for authenticated requests. School forums never expose a follow control in the UI — membership is set at onboarding.

For school forums, `type` is `"school"` and `school` looks like:

```json
{
  "id": 12,
  "name": "СУГС Јосип Броз Тито",
  "city": "Скопје"
}
```

### Thread

```json
{
  "id": 15,
  "title": "Како да се подготвам за матура?",
  "description": "<p>Текст на постот…</p>",
  "upvotes": 8,
  "has_voted": false,
  "is_owner": false,
  "views": 120,
  "is_anonymous": false,
  "comments_count": 4,
  "created_at": "2026-07-18T10:22:00.000000Z",
  "edited_at": null,
  "forum": {
    "id": 3,
    "name": "Државна матура",
    "slug": "drzhavna_matura",
    "type": "general",
    "imageUrl": "https://…/forum.png"
  },
  "author": {
    "id": 1,
    "username": "ana_mk",
    "imageUrl": "https://…/avatar.png",
    "school": {
      "id": 12,
      "name": "СУГС Јосип Броз Тито",
      "city": "Скопје"
    }
  },
  "attachments": [
    {
      "url": "https://…/file.pdf",
      "type": "file"
    }
  ]
}
```

Notes:

- If `is_anonymous` is `true`, `author` is `null`.
- `edited_at` is set when the post was edited; otherwise `null`.
- `attachments[].type` comes from the attachment slug (e.g. image/file/link/video).
- `has_voted` is `true` when the current authenticated user has upvoted this item; guests always get `false`.
- `is_owner` is `true` when the current authenticated user created the thread (including anonymous posts); guests always get `false`.

### Comment (nested tree)

```json
{
  "id": 40,
  "content": "Се согласувам.",
  "parent_id": null,
  "upvotes": 2,
  "has_voted": false,
  "created_at": "2026-07-18T11:00:00.000000Z",
  "edited_at": null,
  "deleted_by": null,
  "author": {
    "id": 2,
    "username": "marko",
    "imageUrl": "https://…/avatar.png",
    "school": null
  },
  "replies": [
    {
      "id": 41,
      "content": "И јас.",
      "parent_id": 40,
      "upvotes": 0,
      "created_at": "2026-07-18T11:05:00.000000Z",
      "edited_at": null,
      "deleted_by": null,
      "author": { "id": 1, "username": "ana_mk", "imageUrl": "…", "school": null },
      "replies": []
    }
  ]
}
```

Top-level comments have `parent_id: null`. Replies nest under `replies`.

---

## Thread list filters

Used by:

- `GET /api/feed`
- `GET /api/p/{slug}/threads`

| Query | Values | Default | Meaning |
|-------|--------|---------|---------|
| `page` | integer ≥ 1 | `1` | Page number |
| `sort` | `trending`, `top`, `newest`, `discussed` | `trending` | Order |
| `time` | `day`, `week`, `month`, `six-months`, `year`, `all` | `all` | Only threads created in this window |

**Sort behavior**

| Value | Order |
|-------|--------|
| `trending` | upvotes ↓, then comments ↓, then newest |
| `top` | upvotes ↓, then newest |
| `newest` | newest first |
| `discussed` | comments_count ↓, then newest |

**Page size:** always **5** threads per page.

Examples:

```
GET /api/feed?sort=trending&time=week
GET /api/feed?page=2&sort=newest
GET /api/p/drzhavna_matura/threads?sort=top&time=month
GET /api/p/drzhavna_matura/threads?sort=newest&page=2
```

---

## Feed

### Personalized / trending feed

```
GET /api/feed
```

**Public** (works for guests). If the SPA sends the session cookie, the feed personalizes for that user.

**Paginated** — same contract as `GET /api/p/{slug}/threads`:

| Query | Values | Default | Meaning |
|-------|--------|---------|---------|
| `page` | integer ≥ 1 | `1` | Page number |
| `sort` | `trending`, `top`, `newest`, `discussed` | `trending` | Order |
| `time` | `day`, `week`, `month`, `six-months`, `year`, `all` | `all` | Only threads created in this window |

**Page size:** always **5** threads per page. Response includes `data`, `links`, and `meta` (`current_page`, `last_page`, `per_page`, `total`).

Examples:

```
GET /api/feed
GET /api/feed?page=2
GET /api/feed?sort=trending&time=week&page=1
```

**Frontend usage (infinite scroll)**

1. Initial: `GET /api/feed` → page 1  
2. Filter change (`sort` / `time`): `GET /api/feed?sort=…&time=…&page=1` → **replace** list  
3. Scroll: `GET /api/feed?page=2&sort=…&time=…` → **append** `data`  
4. Stop when `meta.current_page >= meta.last_page` (or `links.next` is `null`)

**Behavior**

| Who | What you get |
|-----|----------------|
| Guest | Site-wide threads (same sort/time filters as forum lists) |
| Logged in, **no** followed forums | Same as guest — site-wide trending |
| Logged in, **≥1** followed forums | Still the **full site-wide** pool (so 1–2 follows don’t hide everything else). Threads from followed forums get a soft boost (+30), and threads the user previously opened get a smaller boost (+15), then normal sort metrics apply |

Opening a thread via `GET /api/p/{slug}/comments/{id}` while logged in records a row in `thread_views` (and increments the public `views` counter).

```json
{
  "data": [
    {
      "id": 15,
      "title": "Како да се подготвам за матура?",
      "description": "…",
      "upvotes": 8,
      "has_voted": false,
      "is_owner": false,
      "views": 120,
      "is_anonymous": false,
      "comments_count": 4,
      "created_at": "2026-07-18T10:22:00.000000Z",
      "edited_at": null,
      "forum": {
        "id": 3,
        "name": "Државна матура",
        "slug": "drzhavna_matura",
        "imageUrl": "https://…/forum.png"
      },
      "author": { "id": 1, "username": "ana_mk", "imageUrl": "…", "school": null },
      "attachments": []
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/feed?page=1",
    "last": "http://localhost:8000/api/feed?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/feed?page=2"
  },
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 5,
    "total": 12
  }
}
```

---

## Auth

### Start social login (browser redirect)

```
GET /api/auth/{provider}/redirect
```

| Path param | Values |
|------------|--------|
| `provider` | `google`, `facebook` |

**Not a JSON API.** The browser is redirected to Google/Facebook.

Frontend usually does: `window.location = `${API_BASE_URL}/api/auth/google/redirect``.

---

### Social login callback (browser redirect)

```
GET /api/auth/{provider}/callback
```

Handled by the backend after the provider. Sets an **httpOnly session cookie**, then redirects to the frontend:

- Success, onboarding done: `{FRONTEND_URL}/auth/callback?onboarding=complete`
- Success, needs onboarding: `{FRONTEND_URL}/auth/callback?onboarding=required`
- Failure: `{FRONTEND_URL}/login?error=auth_failed`

No JSON body for the SPA to parse on this step.

---

### CSRF cookie (required before mutating requests)

```
GET /sanctum/csrf-cookie
```

Sets `XSRF-TOKEN` cookie. Not under `/api`. Call with `credentials: "include"`.

---

### Current user

```
GET /api/me
```

**Auth required.**

```json
{
  "user": {
    "id": 1,
    "username": "ana_mk",
    "email": "ana@example.com",
    "provider": "google",
    "provider_id": "…",
    "imageUrl": "https://…/avatar.png",
    "onboarding_completed_at": "2026-07-10T12:00:00.000000Z",
    "created_at": "…",
    "updated_at": "…",
    "student_data": {
      "id": 1,
      "user_id": 1,
      "school_id": 12,
      "vocation_id": 3,
      "grade": 3,
      "school": {
        "id": 12,
        "name": "СУГС Јосип Броз Тито",
        "city": {
          "id": 1,
          "name": "Скопје"
        },
        "forum": {
          "id": 40,
          "slug": "sugs_josip_broz_tito_skopje",
          "type": "school"
        }
      },
      "vocation": {
        "id": 3,
        "name": "Гимназиско"
      }
    }
  }
}
```

`student_data` may be `null` for non-students. `onboarding_completed_at` is `null` until onboarding is finished. `school.forum` is included so the profile can link to that school’s forum (`/p/{slug}`).

---

### Profile tab counts

```
GET /api/me/counts
```

**Auth required.** Lightweight badge counts for the profile tabs (no list payloads).

```json
{
  "data": {
    "threads": 12,
    "comments": 34,
    "followed_forums": 5,
    "following_users": 3
  }
}
```

```
GET /api/me/following-users
```

**Auth required.** Users the current user follows (newest username order, max 100). Same public user shape as `/api/u/{username}`.

---

### Public user profile

```
GET /api/u/{username}
```

**Public.** Profile header for a completed-onboarding user. When the request is authenticated (session cookie), includes whether the viewer follows them.

```json
{
  "data": {
    "user": {
      "id": 1,
      "username": "ana_mk",
      "imageUrl": "…",
      "created_at": "…",
      "student_data": {
        "grade": 3,
        "school": {
          "id": 12,
          "name": "СУГС Јосип Броз Тито",
          "city": { "id": 1, "name": "Скопје" },
          "forum": { "id": 40, "slug": "sugs_josip_broz_tito_skopje", "name": "…" }
        },
        "vocation": { "id": 3, "name": "Гимназиско" }
      }
    },
    "counts": {
      "threads": 12,
      "comments": 34,
      "followed_forums": 5,
      "followers": 8
    },
    "is_following": false,
    "is_own_profile": false
  }
}
```

Related list endpoints (same shapes as `/api/me/*`):

- `GET /api/u/{username}/threads`
- `GET /api/u/{username}/comments`
- `GET /api/u/{username}/followed-forums`

Follow / unfollow (**auth required**):

```
POST   /api/u/{username}/follow
DELETE /api/u/{username}/follow
```

```json
{ "data": { "is_following": true, "followers": 9 } }
```

Self-follow returns `422`.

---

### My threads

```
GET /api/me/threads
```

**Auth required.** Threads created by the current user, newest first (max 50). Same `Thread` resource shape as forum/feed lists (includes `forum.type`).

```json
{
  "data": [
    {
      "id": 15,
      "title": "Како да се подготвам за матура?",
      "description": "…",
      "upvotes": 8,
      "has_voted": false,
      "is_owner": false,
      "views": 120,
      "is_anonymous": false,
      "comments_count": 4,
      "created_at": "2026-07-18T10:22:00.000000Z",
      "edited_at": null,
      "forum": {
        "id": 3,
        "name": "Државна матура",
        "slug": "drzhavna_matura",
        "type": "general",
        "imageUrl": "https://…/forum.png"
      },
      "author": { "id": 1, "username": "ana_mk", "imageUrl": "…", "school": null },
      "attachments": [],
      "poll": null
    }
  ]
}
```

---

### My comments

```
GET /api/me/comments
```

**Auth required.** Comments written by the current user, newest first (max 50), each with parent thread + forum context for profile links. Soft-deleted comments are omitted.

```json
{
  "data": [
    {
      "id": 40,
      "content": "Се согласувам.",
      "parent_id": null,
      "upvotes": 2,
      "has_voted": false,
      "created_at": "2026-07-18T11:00:00.000000Z",
      "edited_at": null,
      "deleted_by": null,
      "author": { "id": 1, "username": "ana_mk", "imageUrl": "…", "school": null },
      "thread": {
        "id": 15,
        "title": "Како да се подготвам за матура?",
        "forum": {
          "id": 3,
          "name": "Државна матура",
          "slug": "drzhavna_matura",
          "type": "general",
          "imageUrl": "https://…/forum.png"
        }
      }
    }
  ]
}
```

---

### My followed forums

```
GET /api/me/followed-forums
```

**Auth required.** Forums the current user follows (via `forum_user`), ordered by name. Same `Forum` resource shape as forum detail. Not paginated.

```json
{
  "data": [
    {
      "id": 6,
      "name": "Вештачка интелигенција",
      "slug": "veshtachka_intelegencija",
      "type": "general",
      "school_id": null,
      "imageUrl": "https://…/forum.png",
      "threads_count": 26,
      "members_count": 422,
      "is_following": true,
      "description": "…",
      "bannerUrl": "https://…/banner.png"
    }
  ]
}
```

---

### Logout

```
POST /api/logout
```

**Auth required.** Clears the session cookie.

```json
{
  "message": "Logged out"
}
```

---

### Onboarding

```
PUT /api/onboarding
```

**Auth required.** `Content-Type: application/json`

**Body**

| Field | Type | Rules |
|-------|------|--------|
| `username` | string | required, 3–20 chars, unique |
| `is_student` | boolean | required |
| `school` | string | required if student; format `"School Name\|City Name"` |
| `area` | string | required if student; vocation name (must exist in DB) |
| `year` | string | required if student; one of `Прва`, `Втора`, `Трета`, `Четврта` |

**Example (student)**

```json
{
  "username": "ana_mk",
  "is_student": true,
  "school": "СУГС Јосип Броз Тито|Скопје",
  "area": "Гимназиско",
  "year": "Трета"
}
```

**Example (not a student)**

```json
{
  "username": "ana_mk",
  "is_student": false
}
```

**Success**

```json
{
  "message": "Onboarding saved",
  "user": { }
}
```

`user` is the same full model shape as `/api/me`.

---

## Cities & schools (onboarding dropdown)

```
GET /api/cities
```

**Public.** No pagination. Returns every city with its schools.

```json
{
  "cities": [
    {
      "id": 1,
      "name": "Скопје",
      "schools": [
        { "id": 12, "name": "СУГС Јосип Броз Тито" },
        { "id": 13, "name": "СУГС Раде Јовчевски - Корчагин" }
      ]
    },
    {
      "id": 2,
      "name": "Битола",
      "schools": [
        { "id": 20, "name": "…" }
      ]
    }
  ]
}
```

When submitting onboarding, combine as: `` `${school.name}|${city.name}` ``.

---

## Forums

### Sidebar list (all forums)

```
GET /api/forums
```

**Public.** No pagination — returns **all** forums for the sidebar.

```json
{
  "data": {
    "general": [
      {
        "id": 3,
        "name": "Државна матура",
        "slug": "drzhavna_matura",
        "type": "general",
        "school_id": null,
        "imageUrl": "https://…/forum.png",
        "threads_count": 42,
        "members_count": 120
      }
    ],
    "schools_by_city": [
      {
        "city": {
          "id": 1,
          "name": "Скопје"
        },
        "forums": [
          {
            "id": 10,
            "name": "СУГС Јосип Броз Тито",
            "slug": "josip-broz-tito",
            "type": "school",
            "school_id": 12,
            "imageUrl": "https://…/school.png",
            "threads_count": 8,
            "members_count": 30
          }
        ]
      }
    ]
  }
}
```

- `general` → thematic forums  
- `schools_by_city` → school forums grouped by city (sorted by city name)

---

### Forum detail (banner / metadata only)

```
GET /api/p/{slug}
```

**Public.** No query params. Does **not** return threads — use `/threads` for the list.

| Path | Example |
|------|---------|
| `{slug}` | `drzhavna_matura` |

```json
{
  "data": {
    "forum": {
      "id": 3,
      "name": "Државна матура",
      "slug": "drzhavna_matura",
      "type": "general",
      "school_id": null,
      "imageUrl": "https://…/forum.png",
      "threads_count": 42,
      "members_count": 120,
      "is_following": false,
      "description": "Дискусии за државна матура…",
      "bannerUrl": "https://…/banner.png",
      "school": null
    }
  }
}
```

`is_following` is only present when the request is authenticated.

---

### Follow / unfollow a general forum

```
POST   /api/p/{slug}/follow
DELETE /api/p/{slug}/follow
```

**Auth required.** Only `type: "general"` forums can be followed or unfollowed.

School forums (`type: "school"`) are attached automatically when a student completes onboarding and **cannot** be followed or unfollowed via this API (422). Users can only belong to their own school forum that way.

| Path | Example |
|------|---------|
| `{slug}` | `drzhavna_matura` |

```json
{
  "data": {
    "is_following": true,
    "members_count": 121
  }
}
```

| Status | When |
|--------|------|
| `401` | Guest |
| `422` | School forum (follow or unfollow) |
| `200` | Success (idempotent — following twice stays at one membership) |

---

## Threads

### Paginated threads for a forum

```
GET /api/p/{slug}/threads
```

**Public.** **Only** source of the forum thread list (page 1, filters, infinite scroll).

```json
{
  "data": [
    {
      "id": 15,
      "title": "Како да се подготвам за матура?",
      "description": "…",
      "upvotes": 8,
      "views": 120,
      "is_anonymous": false,
      "comments_count": 4,
      "created_at": "2026-07-18T10:22:00.000000Z",
      "edited_at": null,
      "forum": {
        "id": 3,
        "name": "Државна матура",
        "slug": "drzhavna_matura",
        "imageUrl": "https://…/forum.png"
      },
      "author": { "id": 1, "username": "ana_mk", "imageUrl": "…", "school": null },
      "attachments": []
    }
  ],
  "meta": {
    "current_page": 2,
    "last_page": 3,
    "per_page": 5,
    "total": 12
  },
  "links": {
    "first": "http://localhost:8000/api/p/drzhavna_matura/threads?page=1",
    "last": "http://localhost:8000/api/p/drzhavna_matura/threads?page=3",
    "prev": "http://localhost:8000/api/p/drzhavna_matura/threads?page=1",
    "next": "http://localhost:8000/api/p/drzhavna_matura/threads?page=3"
  }
}
```

**Frontend pattern**

  1. Initial page (parallel):
    - `GET /api/p/{slug}` → banner / forum meta
    - `GET /api/p/{slug}/threads` → page 1 of threads (default or URL filters)
  2. Filter change (`sort` / `time`): `GET /api/p/{slug}/threads?sort=…&time=…&page=1` → **replace** thread list
  3. Scroll: `GET /api/p/{slug}/threads?page=2&sort=…&time=…` → **append** `data`
  4. Stop when `meta.current_page >= meta.last_page` (or `links.next` is `null`)

---

### Single thread + comments

```
GET /api/p/{slug}/comments/{id}
```

**Public.**

| Path | Meaning |
|------|---------|
| `{slug}` | Forum slug |
| `{id}` | Thread id |

If the thread does not belong to that forum → `404`.

```json
{
  "data": {
    "thread": {
      "id": 15,
      "title": "Како да се подготвам за матура?",
      "description": "<p>…</p>",
      "upvotes": 8,
      "views": 120,
      "is_anonymous": false,
      "comments_count": 4,
      "created_at": "2026-07-18T10:22:00.000000Z",
      "edited_at": null,
      "forum": {
        "id": 3,
        "name": "Државна матура",
        "slug": "drzhavna_matura",
        "imageUrl": "https://…/forum.png"
      },
      "author": { "id": 1, "username": "ana_mk", "imageUrl": "…", "school": null },
      "attachments": []
    },
    "comments": [
      {
        "id": 40,
        "content": "Се согласувам.",
        "parent_id": null,
        "upvotes": 2,
        "created_at": "2026-07-18T11:00:00.000000Z",
        "edited_at": null,
        "deleted_by": null,
        "author": { "id": 2, "username": "marko", "imageUrl": "…", "school": null },
        "replies": []
      }
    ]
  }
}
```

`comments` is only top-level comments; replies are nested in each comment’s `replies` array (chronological).

| Query | Values | Default | Meaning |
|-------|--------|---------|---------|
| `sort` | `best`, `newest`, `oldest` | `best` | Top-level comment order |

| `sort` | Order |
|--------|-------|
| `best` | upvotes ↓, then newest |
| `newest` | created_at ↓ |
| `oldest` | created_at ↑ |

```
GET /api/p/drzhavna_matura/comments/15?sort=newest
```

---

## Create thread

```
POST /api/threads
```

**Auth required.** `multipart/form-data` (not JSON). Call `GET /sanctum/csrf-cookie` first, then send `X-XSRF-TOKEN`.

| Field | Type | Rules |
|-------|------|--------|
| `forum_id` | int | required, exists |
| `title` | string | required, 3–200 |
| `description` | string | optional (HTML/text) |
| `is_anonymous` | bool | optional, default false |
| `files[]` | file | optional; image/video/pdf/doc/docx; max 10 images, 1 video, 1 doc. **Order is preserved** in the thread gallery (e.g. video then images, or images then video). |
| `link` | url | optional; stored as attachment `type: link` |
| `poll[question]` | string | optional |
| `poll[options][]` | string[] | required with poll; 2–4 options |
| `poll[duration_days]` | int | required with poll; **1–30** (max one month). Sets `ends_at` from now |

**Exclusivity (same as UI):** link cannot combine with image/video; images + one video are allowed together; document / poll cannot combine.

Files are uploaded with `Media::upload($file, "threads/{id}")` → ImageKit, then saved on `thread_attachments`.

Polls expire after `duration_days` (`ends_at`). One poll per thread.

**Success (`201`)** — `ThreadResource` (includes `attachments` + `poll` when present).

---

### Update thread

```
PUT|POST /api/threads/{id}
```

**Auth required.** Author only. Prefer **`POST` `multipart/form-data`** when adding or removing attachments (PHP file uploads are unreliable on `PUT`). Plain `PUT` JSON still works for title/description only.

| Field | Type | Rules |
|-------|------|--------|
| `title` | string | required; 3–200 characters |
| `description` | string | optional; max 10000 |
| `files[]` | file | optional; same mime/size rules as create |
| `link` | url | optional; adds a new link attachment |
| `remove_attachment_ids[]` | int[] | optional; attachment ids on **this** thread to delete |
| `poll[question]` | string | optional; create or update the thread poll |
| `poll[options][]` | string[] | required with poll; 2–4 options |
| `poll[option_ids][]` | int[] | optional; existing option ids in the same order as `poll[options]` (omit for new options) |
| `poll[duration_days]` | int | required with poll; **1–30**. Sets `ends_at` from now |
| `remove_poll` | bool | optional; deletes the thread poll (cannot combine with `poll`) |

Same exclusivity as create for the **resulting** attachment set after removals + additions (link vs image/video; max 10 images / 1 video / 1 file / 1 link; file cannot combine with a poll). Omitting `poll` leaves an existing poll unchanged. Anonymity is not editable here.

Sets `edited_at` to now. Attachment objects in responses include `id`, `url`, and `type`.

**Success (`200`)** — `ThreadResource`.

| Status | When |
|--------|------|
| `401` | Guest |
| `403` | Not the author |
| `404` | Missing / soft-deleted |
| `422` | Validation failed |

---

### Delete thread

```
DELETE /api/threads/{id}
```

**Auth required.** Author only. Soft-deletes the thread (and its comments), stamps `deleted_by`, and decrements the forum’s `threads_count`.

**Success (`200`)**

```json
{
  "data": {
    "deleted": true
  }
}
```

| Status | When |
|--------|------|
| `401` | Guest |
| `403` | Not the author |
| `404` | Missing / already deleted |

---

## Poll vote

```
POST /api/polls/{id}/vote
```

**Auth required.** Body JSON: `{ "poll_option_id": 1 }`

- One active vote per user per poll (unique `poll_id` + `user_id`)
- Users **may change** their vote to another option while the poll is open
- Results always returned (`votes_count`, `percentage`, `total_votes`, `user_voted_option_id`)
- Rejected after `ends_at` (`422`)

---

## Votes (upvote toggle)

Upvote only (no downvote). Second call from the same user removes the vote.

**Auth required.** Call `GET /sanctum/csrf-cookie` first, then send `X-XSRF-TOKEN`.

### Toggle thread upvote

```
POST /api/threads/{id}/upvote
```

### Toggle comment upvote

```
POST /api/comments/{id}/upvote
```

**Success (`200`)**

```json
{
  "data": {
    "upvotes": 43,
    "has_voted": true
  }
}
```

| Field | Meaning |
|-------|---------|
| `upvotes` | New public counter on the thread/comment |
| `has_voted` | `true` if this request left a vote; `false` if it removed one |

Unique constraint: one vote per user per thread/comment. Feed/forum/thread responses also include `has_voted` when the session user is known.

---

## Comments

### Create comment (or reply)

```
POST /api/threads/{thread}/comments
```

**Auth required.** Call `GET /sanctum/csrf-cookie` first, then send `X-XSRF-TOKEN`. JSON body:

| Field | Type | Rules |
|-------|------|--------|
| `content` | string | required; 1–1000 characters |
| `parent_id` | integer \| null | optional; must be an existing comment on **this same thread** |

Omit `parent_id` (or send `null`) for a top-level comment. Pass a comment id to nest a reply under it (any depth).

**Success (`201`)** — single `Comment` resource (same shape as in the thread tree; `replies` is `[]` for a freshly created node):

```json
{
  "data": {
    "id": 42,
    "content": "Се согласувам.",
    "parent_id": null,
    "upvotes": 0,
    "has_voted": false,
    "created_at": "2026-08-05T12:00:00.000000Z",
    "edited_at": null,
    "deleted_by": null,
    "author": {
      "id": 1,
      "username": "ana_mk",
      "imageUrl": "…",
      "school": null
    },
    "replies": []
  }
}
```

For a reply, `parent_id` is the parent comment’s id. Reload the thread via `GET /api/p/{slug}/comments/{id}` to get the full nested tree.

---

### Update comment

```
PUT /api/comments/{id}
```

**Auth required.** Author only. JSON body:

| Field | Type | Rules |
|-------|------|--------|
| `content` | string | required; 1–1000 characters |

Sets `edited_at` to now. Soft-deleted comments cannot be updated (`404`).

**Success (`200`)** — `Comment` resource (same shape as create).

| Status | When |
|--------|------|
| `401` | Guest |
| `403` | Not the author |
| `404` | Missing / soft-deleted |
| `422` | Validation failed |

---

### Delete comment

```
DELETE /api/comments/{id}
```

**Auth required.** Author only. Soft-deletes the comment and stamps `deleted_by`.

Soft-deleted comments with replies remain in the thread tree as tombstones (`content` is `""`, `deleted_by` is set). Leaf comments disappear from the tree.

**Success (`200`)**

```json
{
  "data": {
    "deleted": true
  }
}
```

| Status | When |
|--------|------|
| `401` | Guest |
| `403` | Not the author |
| `404` | Missing / already deleted |

---

## Media

### Upload file

```
POST /api/media
```

**Auth required.** `multipart/form-data` (not JSON).

| Field | Type | Rules |
|-------|------|--------|
| `file` | file | required; max ~100MB; jpeg/png/webp/gif, mp4/mov, pdf |
| `directory` | string | optional |

**Success (`201`)**

```json
{
  "provider": "imagekit",
  "id": "abc123",
  "path": "uploads/abc123.pdf",
  "url": "https://…/abc123.pdf",
  "name": "notes.pdf",
  "type": "file",
  "size": 204800,
  "mime_type": "application/pdf"
}
```

`type` is one of: `image`, `video`, `file`.

Keep `id` if you need to delete later. When creating threads (future endpoint), you will attach `url` / type to the post.

---

### Delete file

```
DELETE /api/media
```

**Auth required.** JSON body:

```json
{
  "id": "abc123"
}
```

```json
{
  "deleted": true
}
```

---

## Quick index

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| `GET` | `/sanctum/csrf-cookie` | — | CSRF setup |
| `GET` | `/api/auth/{provider}/redirect` | — | Browser redirect |
| `GET` | `/api/auth/{provider}/callback` | — | Browser redirect + session cookie |
| `GET` | `/api/me` | yes | Current user |
| `GET` | `/api/me/counts` | yes | Profile tab badge counts |
| `GET` | `/api/me/threads` | yes | Current user’s threads |
| `GET` | `/api/me/comments` | yes | Current user’s comments (+ thread context) |
| `GET` | `/api/me/followed-forums` | yes | Forums the user follows |
| `GET` | `/api/me/following-users` | yes | Users the current user follows |
| `GET` | `/api/u/{username}` | no | Public user profile + counts |
| `GET` | `/api/u/{username}/threads` | no | User’s threads |
| `GET` | `/api/u/{username}/comments` | no | User’s comments |
| `GET` | `/api/u/{username}/followed-forums` | no | Forums the user follows |
| `POST` | `/api/u/{username}/follow` | yes | Follow user |
| `DELETE` | `/api/u/{username}/follow` | yes | Unfollow user |
| `POST` | `/api/logout` | yes | End session |
| `PUT` | `/api/onboarding` | yes | Save profile |
| `GET` | `/api/cities` | — | Cities + schools |
| `GET` | `/api/forums` | — | Sidebar forums |
| `GET` | `/api/feed` | optional | Paginated personalized / site-wide feed (5/page) |
| `GET` | `/api/p/{slug}` | optional | Forum metadata only (`is_following` when auth) |
| `GET` | `/api/p/{slug}/threads` | — | Paginated threads (page 1, filters, scroll) |
| `GET` | `/api/p/{slug}/comments/{id}` | — | Thread + comment tree (`sort=best\|newest\|oldest`) |
| `POST` | `/api/p/{slug}/follow` | yes | Follow general forum |
| `DELETE` | `/api/p/{slug}/follow` | yes | Unfollow general forum |
| `POST` | `/api/threads` | yes | Create thread (+ files / link / poll) |
| `PUT` | `/api/threads/{id}` | yes | Update thread (author) |
| `DELETE` | `/api/threads/{id}` | yes | Soft-delete thread (author) |
| `POST` | `/api/threads/{id}/comments` | yes | Create comment or nested reply |
| `PUT` | `/api/comments/{id}` | yes | Update comment (author) |
| `DELETE` | `/api/comments/{id}` | yes | Soft-delete comment (author) |
| `POST` | `/api/polls/{id}/vote` | yes | Vote on poll option |
| `POST` | `/api/threads/{id}/upvote` | yes | Toggle thread upvote |
| `POST` | `/api/comments/{id}/upvote` | yes | Toggle comment upvote |
| `POST` | `/api/media` | yes | Upload |
| `DELETE` | `/api/media` | yes | Delete upload |

---

## Not available yet

These are planned but **not** in routes today — do not call them:

- Reports, search, admin JSON APIs

When they ship, this file should be updated.
