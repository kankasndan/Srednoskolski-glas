import { apiFetch, userFacingError } from "@/lib/api";

export function reportErrorMessage(error) {
  if (error?.status === 401) {
    return "Мора да си најавен за да пријавиш содржина.";
  }
  if (error?.status === 422) {
    return (
      error.body?.message ||
      Object.values(error.body?.errors || {})
        .flat()
        .join(" ") ||
      "Провери ја пријавата и обиди се повторно."
    );
  }
  return userFacingError(error, "Неуспешна пријава. Обиди се повторно.");
}

/** @param {number} threadId */
export async function hideThread(threadId) {
  const response = await apiFetch(`/api/threads/${threadId}/hide`, {
    method: "POST",
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to hide thread (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/**
 * @param {number} threadId
 * @param {{ reason: string, details?: string }} payload
 */
export async function reportThread(threadId, payload) {
  const response = await apiFetch(`/api/threads/${threadId}/report`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      reason: payload.reason,
      details: payload.details || undefined,
    }),
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to report thread (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/**
 * @param {number} commentId
 * @param {{ reason: string, details?: string }} payload
 */
export async function reportComment(commentId, payload) {
  const response = await apiFetch(`/api/comments/${commentId}/report`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      reason: payload.reason,
      details: payload.details || undefined,
    }),
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to report comment (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}
