import { apiFetch } from "@/lib/api";

/** Mark a sanction popup as seen so /api/me stops returning it. */
export async function acknowledgeSanction(sanctionId) {
  const res = await apiFetch(`/api/me/sanctions/${encodeURIComponent(sanctionId)}/acknowledge`, {
    method: "POST",
  });

  if (!res.ok && res.status !== 204) {
    const body = await res.json().catch(() => ({}));
    const error = new Error(body.message || `Failed to acknowledge sanction (${res.status})`);
    error.status = res.status;
    throw error;
  }
}

/** Submit an appeal against the current user's sanction. */
export async function submitAppeal(sanctionId, explanation) {
  const res = await apiFetch(`/api/me/sanctions/${encodeURIComponent(sanctionId)}/appeals`, {
    method: "POST",
    body: JSON.stringify({ explanation }),
  });

  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    const error = new Error(body.message || `Failed to submit appeal (${res.status})`);
    error.status = res.status;
    throw error;
  }

  return body.data ?? body;
}
