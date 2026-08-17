# Rules

Read this file before you start anything — every session, every task, before the
first line of code. These rules are binding. If a request conflicts with a rule,
stop and ask me. Never break a rule quietly.

---

## 1. Stack

- **Next.js only.** React components inside Next.js — nothing else. No other
  framework, no other library brought in to solve something Next.js already does.
- **Tailwind only.** All styling goes in Tailwind utility classes. No CSS files,
  no styled-components, no inline style objects for things Tailwind can do.

## 2. The backend is not yours

- The backend belongs to the backend developers. **Do not change anything in
  `backend/`** — no code, no migrations, no config, no installs.
- If a task needs something from the backend — a new endpoint, a change to an
  existing one, a fix, a column — **stop and tell me.** I will get it from them.
- Never fake a backend change on the frontend to work around a missing endpoint.

## 3. Git

- **Always work on the branch I have checked out.** Never switch branches on your
  own, and never write to `master`/`main`.
- **Never commit without telling me first.** When I say to commit, push the
  branch and stop there — I handle merging, the same as a pull request.
- **Never merge and never self-approve.**
- **Do not add yourself as a collaborator**, and keep your name out of commit
  messages and history — no attribution, no co-author lines.

## 4. Design

- **Pixel perfect, always.** When I send a Figma link or a photo, rebuild it to
  match exactly — spacing, sizes, colors, layout. No approximations.
- If the reference and a rule disagree, ask me before you pick one.

## 5. Code style

- **Write it like a human on this team wrote it.** Plain, natural code. No
  obvious boilerplate comments, no over-explaining, no generated-looking patterns.
- **Always follow [aiVShumanExample.txt](aiVShumanExample.txt).** It shows the
  AI-looking version and how it should have been written. Match the second one.
- **Simplest thing that works.** Least code, fewest moving parts. No extra
  layers, helpers, or abstractions the task does not need.
- **Smallest possible components.** Break the UI into the smallest pieces that
  still make sense on their own, and reuse what already exists before writing
  anything new.
- **Repeated logic goes in a hook.** If the same logic shows up in more than one
  component, pull it into a hook and use it everywhere it applies — never copy it
  from one component to another. Use only the hooks you actually need.
- **No magic pixel dimensions.** Let content drive size — `flex`, `gap-*`, plain
  padding. Fixed heights and widths break the moment the content changes.

## 6. Deleting

- **Tell me before you delete anything** — a file, a component, an asset, a prop,
  a block of code. Say what it is and why it is no longer needed, then wait.
- This applies even when the thing is clearly unused.

## 7. When something is unclear

Ask me. Do not guess, and do not decide it on your own. A question costs a minute;
a wrong assumption costs a rebuild.
