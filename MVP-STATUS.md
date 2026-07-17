# Средношколски Глас — MVP статус (завршено vs. преостанато)

> Документ што ја споредува спецификацијата (`Specifikacija_Srednoskolski_Glas.pdf`, верзија 1.0, Мај 2026) со актуелната состојба на кодот во репозиториумот.
>
> Опсег на анализа: `backend/` (Laravel + Sanctum) и `frontend/` (Next.js 16 App Router).
>
> Легенда:
> - ✅ **Завршено** — имплементирано и поврзано (UI ↔ API каде што е применливо)
> - 🟡 **Делумно** — постои дел (само UI, само shema, mock податоци, или недоврзано)
> - ❌ **Не постои** — нема имплементација

---

## 1. Резиме (executive summary)

Проектот има **солидна основа за автентикација и навигација**, но **јадрото на форумот (создавање/читање/уредување на дискусии и коментари) сè уште не е функционално end-to-end**.

Што работи денес:
- OAuth најава (Google, Facebook) преку Sanctum session cookies + onboarding.
- App shell: header, sidebar со тематски и училишни форуми (од mock податоци).
- Визуелно завршени feed и forum-list екрани.
- Backend read-only API за форуми и дискусии + референтни податоци (градови, училишта, подрачја) преку seeders.
- Инфраструктура за прикачување фајлови (ImageKit/S3) — но не поврзана со дискусии.

Најголеми дупки за MVP:
- **Не може да се создаде дискусија ниту коментар** (нема POST endpoints, нема `/new`, нема thread detail страна).
- **Нема upvote логика, пребарување, report, follow, mentions, модерација/админ панел.**
- Feed и форуми се на **mock/hardcoded** податоци, не на вистинскиот backend.
- Главниот layout **не е mobile-responsive** (спецификацијата бара mobile-first).

Груба проценка: MVP-то е **приближно 25–35% завршено** — најмногу во автентикација и статичен UI; најмалку во интерактивните форум функции кои се срцето на платформата.

---

## 2. Опсег на MVP — ставка по ставка

Спецификација, дел 2 („Обем на MVP"):

| # | MVP функција (спец.) | Статус | Каде / зошто |
|---|----------------------|--------|--------------|
| 1 | Web апликација (responsive desktop + мобилен) | 🟡 | Desktop layout завршен; **главниот app shell не е responsive**. Само auth страните имаат `lg:` breakpoints. |
| 2 | Регистрација преку OAuth (Google, Facebook, Apple) | 🟡 | Google + Facebook работат. **Apple = „Наскоро" (disabled)**; не е конфигуриран во `config/services.php`. |
| 3 | Псевдоним + училиште како јавен идентитет | ✅ | Onboarding зачувува `username` + `student_data` (училиште, подрачје, година). Автор payload носи `username`, `imageUrl`, `school`. |
| 4 | Форуми по тема и по училиште | 🟡 | Backend ги враќа (`GET /api/forums`), seeders ги полнат. **Но frontend чита од mock JSON** (`USE_MOCK = true`), не од API. |
| 5 | Создавање/уредување/бришење на дискусии и коментари | ❌ | Нема POST/PUT/DELETE routes за threads/comments; нема `CommentController`; нема `/new` страна ниту UI за коментари. |
| 6 | Upvote систем (без downvote) | ❌ | Постои само `threads.upvotes` бројач (статичен). Нема `votes` табела, нема endpoint, нема toggle логика, UI копчињата немаат handler. |
| 7 | Пребарување низ дискусии и коментари | ❌ | Нема `/search` страна, нема search endpoint. `SearchBar` е статичен `<input>`. |
| 8 | Прикачување текст/слики/линкови/видеа/фајлови (PDF, DOC) | 🟡 | `POST /api/media` (ImageKit/S3) прима слики/видео/**PDF**, но **не DOC**. Нема UI за upload и **нема начин да се прикачи медиум на дискусија** преку API. |
| 9 | Систем за пријава (Report) | ❌ | Нема `reports` табела, controller, route, ниту UI. |
| 10 | Рачна модерација од админ тим | ❌ | Нема админ панел, роли, банови. `users.type` постои но не се користи. |
| 11 | Македонски јазик | 🟡 | Најголем дел од UI е на македонски. Исклучоци: `/` stub, auth callback („Signing you in..."), `<html lang="en">`. |

---

## 3. Страници (спец. дел 3) vs. имплементација

| Спец. страна | URL | Frontend статус | Детали |
|--------------|-----|-----------------|--------|
| 3.1 Главна страна (Home) | `/` | 🟡 | Вистинскиот feed е на `/feed` (`AppShell` + `CommunityBanner` + `FeedThreads`). `/` е англиски stub. Feed е **hardcoded (16 постови)**, филтрите не влијаат. |
| 3.2 Страна на форум | `/p/<slug>` | 🟡 | `/p/[slug]` постои: banner, филтри, листа/empty state. Метаподатоци од mock forums; дискусии **само за `drzhavna_matura`** од `forum-page-mock.json`. |
| 3.3 Страна на дискусија (Thread) | `/p/<форум>/<id>` | ❌ | **Не постои route.** Нема thread detail, нема коментари, нема nested replies, нема ⋮ мени, нема Follow, views, share. |
| 3.4 Започни нова дискусија | `/new` | ❌ | **Не постои route.** Нема rich text editor, forum picker, ниту attachments. Сите „Започни дискусија" копчиња се неактивни. |
| 3.5 Пребарување | `/search` | ❌ | Не постои. Нема live search, highlight, филтри. |
| 3.6 Recent Comments | `/recent` | ❌ | Не постои. Sidebar „Најнови дискусии" е копче без `href`. |
| 3.7 Регистрација и најава | `/login`, `/register`, `/register/onboarding` | ✅ | Комплетно UI, поврзано со backend. OAuth копчиња, onboarding форма (псевдоним, училиште, подрачје, година, Terms). Има и `onboarding_2` за аватар (само локален preview). |

### Header (спец. 3.1)
- ✅ Лого (→ `/feed`), search bar во средина, десно auth (Најави се / Регистрација или аватар + dropdown со „Одјави се").
- 🟡 Search bar е **само визуелен** (без state/submit/routing).

### Sidebar (спец. 3.1)
| Ставка | Статус |
|--------|--------|
| Home (Почетна) | ✅ → `/feed` |
| Recent Comments | ❌ „Најнови дискусии" копче без навигација |
| Search all threads | ❌ нема ставка; header search нефункционален |
| Start new thread | ❌ нема во sidebar |
| Topic Forums (динамички по активност) | 🟡 се прикажуваат (mock), **не се сортирани по активност** |
| School Forums (по град) | 🟡 се прикажуваат (mock, скратена листа) |

---

## 4. Кориснички пермисии (спец. дел 4)

| Акција | Спец. (регистриран) | Статус |
|--------|---------------------|--------|
| Читање дискусии/коментари | Да | 🟡 API постои; frontend на mock |
| Пребарување | Да | ❌ |
| Креирање дискусија | Да | ❌ |
| Коментирање | Да | ❌ |
| Upvote | Да | ❌ |
| Edit/Delete свои постови | Да | ❌ |
| Report | Да | ❌ |
| Follow thread | Да | ❌ |

> Забелешка: нема слој за авторизација (policies/gates) кој ги спроведува овие пермисии, бидејќи операциите на пишување сè уште не постојат.

---

## 5. Форуми (спец. дел 5)

| Ставка | Статус | Детали |
|--------|--------|--------|
| Topic Forums (16 тематски) | 🟡 | `ForumSeeder` полни ~16 општи форуми; backend ги враќа. Frontend прикажува mock подмножество. **Динамичкото сортирање по активност (7 дена) не е имплементирано.** |
| School Forums (по училиште/град) | 🟡 | Seeder создава форум по училиште; групирани по град во API. Frontend прикажува скратен mock список. |
| Автоматски badge со училиште | ✅ | `student_data` го врзува корисникот со училиште; авторот носи `school`. |
| Официјална листа училишта од МОН | 🟡 | Постои голема seed листа (~90+) и hardcoded `lib/schools.js`, но не е потврдена официјална листа. |

---

## 6. Клучни функционалности (спец. дел 6)

| 6.x | Функција | Статус | Детали |
|-----|----------|--------|--------|
| 6.1 | Upvote (само up, toggle, јавно видлив) | ❌ | Само статичен бројач на `threads`. Нема votes табела/endpoint/toggle. |
| 6.2 | Mentions (@) со autocomplete | ❌ | Нема backend ниту UI. |
| 6.3 | Прикачување содржина (слики/линкови/видео/фајлови, virus scan) | 🟡 | ImageKit/S3 upload постои (слики, видео, PDF). Нема DOC/DOCX, нема link preview, нема YouTube/TikTok embed, нема virus scan, нема поврзување со дискусии. |
| 6.4 | Edit / Delete (со ознака „уредено"/„избришано") | ❌ | Нема mutation endpoints ниту UI. |
| 6.5 | Report (причини, текст поле, до админ) | ❌ | Нема. |
| 6.6 | Follow thread (визуелно во MVP) | ❌ | Нема копче ниту табела. |

---

## 7. Модерација (спец. дел 7)

| Ставка | Статус |
|--------|--------|
| Админ панел (листа reports, преглед, акции) | ❌ Не постои |
| Историја на акции по корисник | ❌ |
| Proactive отстранување содржина | ❌ |
| Систем на санкции (предупредување/бан 7 дена/траен) | ❌ Нема ban полиња во `users` |
| Community Guidelines (документ/страна) | ❌ Terms се само стилизиран текст, без вистински линкови |

---

## 8. Технички спецификации (спец. дел 8)

| Барање | Спец. | Статус |
|--------|-------|--------|
| Frontend framework | React/Next.js + Tailwind, mobile-first | 🟡 Next.js 16 + Tailwind ✅; **не mobile-first** за главниот app |
| Backend | Node ИЛИ Python; PostgreSQL; Redis; S3 | 🟡 Избран **Laravel (PHP)** — валидна алтернатива. БД е **SQLite** (default), не Postgres. Нема Redis. S3 поддржано преку media драјвер. |
| Автентикација | OAuth 2.0; JWT; 30-дневна сесија | 🟡 OAuth ✅. Користи **Sanctum session cookies (httpOnly)** наместо JWT — побезбедно за SPA, но отстапува од спец. `remember: true` дава долготрајна сесија. |
| Безбедност | HTTPS, rate limiting, CSRF, XSS, GDPR, delete account | 🟡 CSRF ✅ (Sanctum). **Нема rate limiting, нема delete-account, GDPR не адресиран.** |
| Перформанси | <2s render, 1000 конкурентни, lazy load, pagination/infinite scroll | ❌ Нема pagination/infinite scroll; feed е фиксна листа; неоптимизирано. |

---

## 9. Backend инвентар (актуелно)

**База (миграции):** `users`, `sessions`, `cities`, `schools`, `vocations`, `forums`, `threads`, `thread_attachments`, `comments`, `forum_user`, `student_data`, + Laravel системски табели.
- ❌ Недостасуваат: `votes`, `reports`, `follows`, `mentions`, admin/ban табели.

**Модели:** `User`, `Forum`, `Thread`, `Comment`, `ThreadAttachment`, `City`, `School`, `Vocation`, `StudentData`.

**Контролери:**
- ✅ `ForumController` (`index`, `show`) — листа + форум со сортирани/филтрирани дискусии.
- ✅ `ThreadController` (`show`) — дискусија + вгнездени коментари (само читање).
- ✅ `CityController`, `MediaController` (upload/delete), Auth: `SocialLogin`, `Me`, `Logout`, `Onboarding`.
- ❌ `FeedController` е празен stub; нема `CommentController`.

**Routes (`/api`):**

| Метод | Патека | Auth | Статус |
|-------|--------|------|--------|
| GET | `/auth/{provider}/redirect` · `/callback` | web | ✅ |
| PUT | `/onboarding` | sanctum | ✅ |
| GET | `/forums` · `/cities` | — | ✅ |
| GET | `/me` | sanctum | ✅ |
| POST | `/logout` | sanctum | ✅ |
| GET | `/p/{forum:slug}` | — | ✅ |
| GET | `/p/{forum:slug}/comments/{thread:id}` | — | ✅ |
| POST/DELETE | `/media` | sanctum | ✅ |

- ❌ Нема: `POST /threads`, `PUT/DELETE /threads/{id}`, коментар CRUD, upvote, search, report, follow, admin.

**Seeders:** градови, училишта, 21 подрачје, 16+ форуми, 9 тест корисници, student_data, forum памоќ, 12 дискусии, прилози, коментари.

---

## 10. Frontend инвентар (актуелно)

**Страници:** `/` (stub), `/feed` (mock), `/login`, `/register`, `/register/onboarding`, `/register/onboarding_2`, `/auth/callback`, `/p/[slug]` (mock), `/imeNaRuta` (dev stub).

**Податоци:**
- Форуми (sidebar): **mock JSON** (`USE_MOCK = true` во `api/forums.js`).
- Feed: **hardcoded** во `FeedThreads.js`.
- Форум дискусии: mock само за `drzhavna_matura`.
- Auth / onboarding / user: **вистински API** преку `lib/api.js` (Sanctum cookies).
- Училишта во onboarding: **hardcoded** `lib/schools.js`.

**Отсутни компоненти:** нема `ThreadDetail`, `Comment`, `Reply`, `PostEditor`/rich-text, `MentionInput`, `UpvoteButton`, `ThreadMenu` (⋮), `AttachmentUpload`.

---

## 11. Приоритизирана листа на преостанато за MVP

### P0 — Јадро на форумот (без ова нема MVP)
1. **Поврзи frontend со вистински API** — исклучи `USE_MOCK`, замени hardcoded feed и школска листа со `/api/*`.
2. **Создавање дискусија**: `POST /api/threads` + `/new` страна (forum picker, title, body). Валидација: форум задолжителен, title 3–200.
3. **Thread detail страна** `/p/<slug>/<id>` + читање на постоечкиот `ThreadController@show`.
4. **Коментари**: `CommentController` со `POST` (+ nested replies), UI поле за коментар и приказ на дрво.
5. **Upvote**: `votes` табела + toggle endpoint за threads и comments + врзани UI копчиња.
6. **Edit / Delete** на сопствени дискусии и коментари (endpoints + policies + ⋮ мени).

### P1 — Основни MVP функции
7. **Пребарување** `/search` + backend search (наслов/тело/коментари) со филтри и highlight.
8. **Report** систем: табела + endpoint + ⋮ Report UI.
9. **Recent Comments** `/recent` страна + endpoint.
10. **Прикачување на дискусија**: врзи `POST /api/media` со создавање дискусија; додади UI (слика/фајл/линк preview/видео embed).
11. **Динамичко сортирање форуми по активност** (последни 7 дена).
12. **Follow thread** (визуелно копче + табела).
13. **Mentions (@)** autocomplete.

### P2 — Модерација и полирање
14. **Админ панел** + роли (`users.type`) + банови/санкции + policies.
15. **Rate limiting** (регистрација, постови, коментари).
16. **Mobile-responsive** главен layout (hamburger/collapsible sidebar).
17. **Apple OAuth**, delete-account/GDPR, Community Guidelines/Terms страни.
18. **Pagination / infinite scroll**, lazy loading, virus scan за прилози.
19. Ситни: `<html lang="mk">`, преведи преостанати англиски стрингови, отстрани dev stub routes (`/`, `/imeNaRuta`).

---

## 12. Отстапувања од спецификацијата (свесни одлуки)

- **Backend = Laravel/PHP** наместо предложениот Node/Python — прифатлива алтернатива.
- **Session cookies (Sanctum)** наместо JWT — побезбедно за SPA (httpOnly, XSS-отпорно), но отстапува од текстот на спец.
- **SQLite** во развој наместо PostgreSQL — треба да се потврди Postgres за продукција.
- Аватарите се доделуваат автоматски (default SVG) наместо upload — onboarding avatar чекорот е само локален preview.

---

*Генерирано со анализа на целиот код во `backend/` и `frontend/` наспроти `Specifikacija_Srednoskolski_Glas.pdf`.*
