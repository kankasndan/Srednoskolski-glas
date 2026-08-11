import Cookies from "js-cookie";
import { API_BASE_URL, apiFetch, ensureCsrfCookie } from "@/lib/api";
import { normalizeEmbedLink } from "@/lib/embeds";

export async function getThread(forumSlug, threadId, { sort = "best" } = {}) {
  const params = new URLSearchParams();
  if (sort) params.set("sort", sort);
  const query = params.toString();
  const res = await apiFetch(
    `/api/p/${forumSlug}/comments/${threadId}${query ? `?${query}` : ""}`,
  );

  if (res.status === 404) return null;
  if (!res.ok) throw new Error(`Failed to load thread: ${res.status}`);

  const { data } = await res.json();

  return data;
}

/**
 * @param {{
 *   forumId: number,
 *   title: string,
 *   description?: string,
 *   isAnonymous?: boolean,
 *   files?: File[],
 *   link?: string,
 *   poll?: { question: string, options: string[], duration_days: number } | null,
 * }} payload
 */
export async function createThread(payload) {
  await ensureCsrfCookie();

  const formData = new FormData();
  formData.append("forum_id", String(payload.forumId));
  formData.append("title", payload.title);
  formData.append("description", payload.description ?? "");
  formData.append("is_anonymous", payload.isAnonymous ? "1" : "0");

  const link = payload.link ? normalizeEmbedLink(payload.link) : null;
  if (link) {
    formData.append("link", link);
  }

  for (const file of payload.files ?? []) {
    formData.append("files[]", file);
  }

  if (payload.poll?.question && payload.poll.options?.length) {
    formData.append("poll[question]", payload.poll.question);
    formData.append("poll[duration_days]", String(payload.poll.duration_days ?? 3));
    payload.poll.options.forEach((option, index) => {
      formData.append(`poll[options][${index}]`, option);
    });
  }

  const response = await fetch(`${API_BASE_URL}/api/threads`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "X-XSRF-TOKEN": Cookies.get("XSRF-TOKEN") ?? "",
    },
    body: formData,
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to create thread (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/**
 * @param {number} threadId
 * @param {{
 *   title: string,
 *   description?: string,
 *   files?: File[],
 *   link?: string,
 *   removeAttachmentIds?: number[],
 *   poll?: {
 *     question: string,
 *     options: string[],
 *     option_ids?: (number|null)[],
 *     duration_days: number,
 *   } | null,
 *   removePoll?: boolean,
 * }} payload
 */
export async function updateThread(threadId, payload) {
  await ensureCsrfCookie();

  const formData = new FormData();
  formData.append("title", payload.title);
  formData.append("description", payload.description ?? payload.content ?? "");

  const link = payload.link ? normalizeEmbedLink(payload.link) : null;
  if (link) {
    formData.append("link", link);
  }

  for (const file of payload.files ?? []) {
    formData.append("files[]", file);
  }

  for (const id of payload.removeAttachmentIds ?? []) {
    formData.append("remove_attachment_ids[]", String(id));
  }

  if (payload.removePoll) {
    formData.append("remove_poll", "1");
  } else if (payload.poll?.question && payload.poll.options?.length) {
    formData.append("poll[question]", payload.poll.question);
    formData.append("poll[duration_days]", String(payload.poll.duration_days ?? 3));
    payload.poll.options.forEach((option, index) => {
      formData.append(`poll[options][${index}]`, option);
      const optionId = payload.poll.option_ids?.[index];
      if (optionId != null) {
        formData.append(`poll[option_ids][${index}]`, String(optionId));
      }
    });
  }

  // POST multipart — PHP does not populate files reliably on PUT.
  const response = await fetch(`${API_BASE_URL}/api/threads/${threadId}`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "X-XSRF-TOKEN": Cookies.get("XSRF-TOKEN") ?? "",
    },
    body: formData,
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to update thread (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/** @param {number} threadId */
export async function toggleThreadVote(threadId) {
  const response = await apiFetch(`/api/threads/${threadId}/upvote`, {
    method: "POST",
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to vote on thread (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}

/** @param {number} threadId */
export async function deleteThread(threadId) {
  const response = await apiFetch(`/api/threads/${threadId}`, {
    method: "DELETE",
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to delete thread (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body.data;
}
