# Winner HC HR Ops Desk — GitHub Pages + Supabase

This package converts the HR Ops Desk from browser-only storage to a shared cloud workspace.

## Architecture

- Frontend: GitHub Pages
- Database/API/Realtime: Supabase Postgres + Data API + Realtime
- Login: Supabase Auth
- User creation/removal: Supabase Edge Function
- Master Dashboard: shared task data from the central workspace

Supabase provides Postgres, Auth, APIs and Realtime. GitHub Pages hosts the static HTML/JavaScript frontend.

## Files

- `index.html` — HR Ops Desk
- `config.js` — Supabase project URL/key
- `supabase/schema.sql` — database, RLS policies and Realtime setup
- `supabase/functions/manage-user/index.ts` — Admin-only user management
- `.nojekyll`
- `.gitignore`

## 1. Create a Supabase project

Create a project at Supabase.

## 2. Run the SQL

Open Supabase Dashboard -> SQL Editor.

Run:

`supabase/schema.sql`

If `alter publication ... add table` reports that a table is already in the publication, that table is already enabled for Realtime.

## 3. Create the first Admin

Supabase Dashboard -> Authentication -> Users -> Add user.

Use:

- Email: `admin@winnerhc.local`
- Password: choose a strong password

Copy the user's UUID.

At the bottom of `supabase/schema.sql`, copy the commented Admin INSERT, replace `YOUR_ADMIN_UUID`, and run it.

The app login username is `admin`. The internal synthetic email is not shown to users.

## 4. Deploy the Edge Function

Install Supabase CLI and log in.

Commands:

```bash
supabase login
supabase link --project-ref YOUR_PROJECT_REF
supabase functions deploy manage-user
```

The hosted Supabase Edge Function already receives the standard Supabase environment variables required by the function.

## 5. Configure the frontend

Open `config.js`.

Replace:

- `YOUR_PROJECT_REF`
- `YOUR_SUPABASE_PUBLISHABLE_OR_ANON_KEY`

Use the Project URL and Publishable/Anon key from Supabase Dashboard -> Project Settings -> API.

Never place the `service_role` key in `config.js` or GitHub Pages.

## 6. Upload to GitHub

Upload these files/folders to the repository root:

- `index.html`
- `config.js`
- `.nojekyll`
- `.gitignore`
- `supabase/`

Then GitHub repository -> Settings -> Pages:

- Source: Deploy from a branch
- Branch: `main`
- Folder: `/ (root)`

## 7. Login

Open the GitHub Pages URL.

Login with:

- Username: `admin`
- Password: the password created in Supabase Auth

Then open Settings -> Users & access to create employee accounts and assign tab permissions, including Master Dashboard.

## Important production note

The current frontend keeps the application's operational modules in one shared workspace JSON document for compatibility with the existing single-file app. This gives central cross-device sync and Realtime refresh, but very heavy simultaneous editing can create last-write-wins conflicts.

For a larger rollout, the next database version should normalize `tasks`, `candidates`, `openings`, and `documents` into separate Postgres tables. That is the recommended final production architecture.
