# Srednoskolski Glas — Backend API

> **Frontend docs:** full per-endpoint reference with params and example JSON → [`API.md`](./API.md)

## API conventions

All `/api/*` JSON responses follow a single contract so the frontend can parse them the same way everywhere.

### Success envelope

**Single resource / compound payload**

```json
{
  "data": { }
}
```

**Paginated list** (e.g. `GET /api/p/{slug}/threads`)

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
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

Always serialize models through `app/Http/Resources/*Resource.php` (`User`, `Forum`, `Thread`, `Comment`, `ThreadAttachment`). Controllers must not hand-build author/thread arrays.

### Error envelope

```json
{
  "message": "Human-readable summary."
}
```

Validation failures (`422`) additionally include field errors:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### Key read endpoints

| Method | Path | Notes |
|--------|------|-------|
| `GET` | `/api/forums` | **All** forums (sidebar). No pagination. `{ data: { general, schools_by_city } }` |
| `GET` | `/api/p/{slug}` | Forum metadata only (banner/description). No threads. |
| `GET` | `/api/p/{slug}/threads` | Paginated threads for that forum (5/page). Query: `page`, `sort`, `time` |
| `GET` | `/api/p/{slug}/comments/{id}` | Single thread + nested comments |

### Thread list filters

On `/api/p/{slug}/threads` only:

| Param | Values | Default |
|-------|--------|---------|
| `sort` | `trending`, `top` (most upvoted), `newest`, `discussed` | `trending` |
| `time` | `day`, `week`, `month`, `six-months`, `year`, `all` | `all` |
| `page` | integer ≥ 1 | `1` |

Examples:

- Most upvoted this month: `GET /api/p/drzhavna_matura/threads?sort=top&time=month`
- Newest in a forum: `GET /api/p/drzhavna_matura/threads?sort=newest`
- Page 2 while scrolling: `GET /api/p/drzhavna_matura/threads?page=2`

> **Feed / FYP** (`/api/feed`) is a separate endpoint and will use its own ranking algorithms. Do not overload forum thread lists with personalized feed logic.

## About Laravel

See the default Laravel docs below for framework overview.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
