import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
};

Deno.serve(async (req) => {
  if (req.method === "OPTIONS") return new Response("ok", { headers: corsHeaders });

  try {
    const authHeader = req.headers.get("Authorization");
    if (!authHeader) throw new Error("Missing authorization");

    const supabaseUrl = Deno.env.get("SUPABASE_URL")!;
    const serviceRoleKey = Deno.env.get("SUPABASE_SERVICE_ROLE_KEY")!;
    const anonKey = Deno.env.get("SUPABASE_ANON_KEY")!;

    const callerClient = createClient(supabaseUrl, anonKey, {
      global: { headers: { Authorization: authHeader } },
    });
    const adminClient = createClient(supabaseUrl, serviceRoleKey);

    const { data: { user }, error: userError } = await callerClient.auth.getUser();
    if (userError || !user) throw new Error("Invalid session");

    const { data: caller, error: callerError } = await adminClient
      .from("profiles")
      .select("role, workspace_id")
      .eq("id", user.id)
      .single();

    if (callerError || caller?.role !== "Admin") throw new Error("Admin access required");

    const body = await req.json();
    if (body.workspaceId !== caller.workspace_id) throw new Error("Workspace mismatch");

    if (body.action === "create") {
      const username = String(body.username || "").trim().toLowerCase();
      const email = `${username}@winnerhc.local`;

      const { data: created, error: createError } = await adminClient.auth.admin.createUser({
        email,
        password: body.password,
        email_confirm: true,
      });
      if (createError) throw createError;

      const { error: profileError } = await adminClient.from("profiles").insert({
        id: created.user.id,
        workspace_id: caller.workspace_id,
        full_name: body.fullName,
        username,
        role: body.role,
        perms: body.role === "Admin" ? ["dash","tasks","rec","expiry","kpi","master","leave"] : body.perms,
      });

      if (profileError) {
        await adminClient.auth.admin.deleteUser(created.user.id);
        throw profileError;
      }

      return new Response(JSON.stringify({ ok: true }), {
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    if (body.action === "delete") {
      if (body.userId === user.id) throw new Error("You cannot remove your own account");

      const { data: target } = await adminClient
        .from("profiles")
        .select("role")
        .eq("id", body.userId)
        .single();

      if (target?.role === "Admin") {
        const { count } = await adminClient
          .from("profiles")
          .select("*", { count: "exact", head: true })
          .eq("workspace_id", caller.workspace_id)
          .eq("role", "Admin");
        if ((count || 0) <= 1) throw new Error("Cannot remove the last Admin");
      }

      const { error } = await adminClient.auth.admin.deleteUser(body.userId);
      if (error) throw error;

      return new Response(JSON.stringify({ ok: true }), {
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      });
    }

    throw new Error("Unsupported action");
  } catch (error) {
    return new Response(JSON.stringify({ ok: false, error: error.message }), {
      status: 400,
      headers: { ...corsHeaders, "Content-Type": "application/json" },
    });
  }
});
