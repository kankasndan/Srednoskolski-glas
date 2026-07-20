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
  "school": null
}
```

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

### Comment (nested tree)

```json
{
  "id": 40,
  "content": "Се согласувам.",
  "parent_id": null,
  "upvotes": 2,
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

- `GET /api/p/{slug}`
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
GET /api/p/drzhavna_matura/threads?sort=top&time=month
GET /api/p/drzhavna_matura/threads?sort=newest&page=2
GET /api/p/drzhavna_matura?sort=discussed&time=week
```

> Cross-forum feed / FYP (`/api/feed`) is **not** implemented yet. Do not use forum thread lists as a personalized feed.

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

`student_data` may be `null` for non-students. `onboarding_completed_at` is `null` until onboarding is finished.

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

### Forum page (forum + first page of threads)

```
GET /api/p/{slug}
```

**Public.**

| Path | Example |
|------|---------|
| `{slug}` | `drzhavna_matura` |

Query: `page`, `sort`, `time` (see [Thread list filters](#thread-list-filters)).

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
      "description": "Дискусии за државна матура…",
      "bannerUrl": "https://…/banner.png",
      "school": null
    },
    "threads": [
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
    ]
  },
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 5,
    "total": 12
  },
  "links": {
    "first": "…",
    "last": "…",
    "prev": null,
    "next": "…"
  }
}
```

Use this for the initial forum page load (banner + first 5 threads).

---

## Threads

### Paginated threads for a forum (infinite scroll)

```
GET /api/p/{slug}/threads
```

**Public.** Same filters as forum show. Prefer this for loading page 2+ while scrolling.

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

1. Load forum: `GET /api/p/{slug}` → render banner + `data.threads`
2. On scroll: `GET /api/p/{slug}/threads?page=2&sort=…&time=…` → append `data`
3. Stop when `meta.current_page >= meta.last_page` (or `links.next` is `null`)

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

`comments` is only top-level comments; replies are nested in each comment’s `replies` array. Currently ordered newest first. Comment sort params (`best` / `newest` / `oldest`) are not implemented yet.

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
| `POST` | `/api/logout` | yes | End session |
| `PUT` | `/api/onboarding` | yes | Save profile |
| `GET` | `/api/cities` | — | Cities + schools |
| `GET` | `/api/forums` | — | Sidebar forums |
| `GET` | `/api/p/{slug}` | — | Forum + threads page 1 |
| `GET` | `/api/p/{slug}/threads` | — | Paginated threads (scroll) |
| `GET` | `/api/p/{slug}/comments/{id}` | — | Thread + comment tree |
| `POST` | `/api/media` | yes | Upload |
| `DELETE` | `/api/media` | yes | Delete upload |

---

## Not available yet

These are planned but **not** in routes today — do not call them:

- `GET /api/feed` (cross-forum FYP)
- `POST /api/threads` (create discussion)
- Comment create / edit / delete
- Votes, reports, search, follow, admin

When they ship, this file should be updated.
