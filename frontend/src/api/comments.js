import { apiFetch } from "@/lib/api";

/** @param {number} commentId */
export async function toggleCommentVote(commentId) {
  const response = await apiFetch(`/api/comments/${commentId}/upvote`, {
    method: "POST",
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to vote on comment (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/**
 * @param {number} threadId
 * @param {{ content: string, parentId?: number | null }} payload
 */
export async function createComment(threadId, payload) {
  const response = await apiFetch(`/api/threads/${threadId}/comments`, {
    method: "POST",
    body: JSON.stringify({
      content: payload.content,
      parent_id: payload.parentId ?? null,
    }),
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to create comment (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/**
 * @param {number} commentId
 * @param {{ content: string }} payload
 */
export async function updateComment(commentId, payload) {
  const response = await apiFetch(`/api/comments/${commentId}`, {
    method: "PUT",
    body: JSON.stringify({
      content: payload.content,
    }),
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to update comment (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/** @param {number} commentId */
export async function deleteComment(commentId) {
  const response = await apiFetch(`/api/comments/${commentId}`, {
    method: "DELETE",
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to delete comment (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}
