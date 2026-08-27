import { apiFetch } from "@/lib/api";

/** Submit a rating (1–5) and optional message from the about-page feedback popup. */
export async function submitFeedback({ rating, message }) {
  const res = await apiFetch("/api/feedback", {
    method: "POST",
    body: JSON.stringify({
      rating,
      message: message ? message : null,
    }),
  });

  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    const error = new Error(body.message || `Failed to submit feedback (${res.status})`);
    error.status = res.status;
    throw error;
  }

  return body.data ?? body;
}
