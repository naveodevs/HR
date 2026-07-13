-- Winner HC HR Ops Desk - Supabase setup
-- Run in Supabase Dashboard -> SQL Editor.

create extension if not exists pgcrypto;

create table if not exists public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  workspace_id text not null default 'winnerhc-main',
  full_name text not null,
  username text not null,
  role text not null check (role in ('Admin','Member')),
  perms jsonb not null default '[]'::jsonb,
  created_at timestamptz not null default now(),
  unique (workspace_id, username)
);

create table if not exists public.workspace_state (
  workspace_id text primary key,
  data jsonb not null default '{}'::jsonb,
  updated_at timestamptz not null default now()
);

alter table public.profiles enable row level security;
alter table public.workspace_state enable row level security;

create or replace function public.current_profile()
returns public.profiles
language sql
stable
security definer
set search_path = public
as $$
  select p from public.profiles p where p.id = auth.uid()
$$;

drop policy if exists "profiles readable in own workspace" on public.profiles;
create policy "profiles readable in own workspace"
on public.profiles for select
to authenticated
using (
  workspace_id = (select (public.current_profile()).workspace_id)
);

drop policy if exists "admins update profiles" on public.profiles;
create policy "admins update profiles"
on public.profiles for update
to authenticated
using (
  (select (public.current_profile()).role) = 'Admin'
  and workspace_id = (select (public.current_profile()).workspace_id)
)
with check (
  (select (public.current_profile()).role) = 'Admin'
  and workspace_id = (select (public.current_profile()).workspace_id)
);

drop policy if exists "workspace readable by members" on public.workspace_state;
create policy "workspace readable by members"
on public.workspace_state for select
to authenticated
using (
  workspace_id = (select (public.current_profile()).workspace_id)
);

drop policy if exists "workspace writable by members" on public.workspace_state;
create policy "workspace writable by members"
on public.workspace_state for update
to authenticated
using (
  workspace_id = (select (public.current_profile()).workspace_id)
)
with check (
  workspace_id = (select (public.current_profile()).workspace_id)
);

insert into public.workspace_state (workspace_id, data)
values (
  'winnerhc-main',
  '{
    "settings":{"reminderEmail":"hr@winnerhc.com","reminderDays":[30,20,15,10]},
    "docs":[],
    "openings":[],
    "tasks":[],
    "candidates":[],
    "nextId":1000
  }'::jsonb
)
on conflict (workspace_id) do nothing;

-- Realtime
alter publication supabase_realtime add table public.workspace_state;
alter publication supabase_realtime add table public.profiles;

-- FIRST ADMIN SETUP:
-- 1) Supabase Dashboard -> Authentication -> Users -> Add user
--    Email: admin@winnerhc.local
--    Password: choose a strong password
-- 2) Copy the created user's UUID and run the INSERT below after replacing YOUR_ADMIN_UUID.

-- insert into public.profiles (id, workspace_id, full_name, username, role, perms)
-- values (
--   'YOUR_ADMIN_UUID',
--   'winnerhc-main',
--   'Administrator',
--   'admin',
--   'Admin',
--   '["dash","tasks","rec","expiry","kpi","master","leave"]'::jsonb
-- );
