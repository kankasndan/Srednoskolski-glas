import Cookies from "js-cookie";
import { API_BASE_URL, apiFetch, ensureCsrfCookie } from "@/lib/api";
import { normalizeEmbedLink } from "@/lib/embeds";

const USE_MOCK = false;

const MOCK_THREADS = {
  "drzhavna_matura/1": "/MOCK_JSON/thread-drzhavna-matura-mock.json",
  "opshti_diskusii/101": "/MOCK_JSON/thread-opshti-diskusii-mock.json",
  "opshti_diskusii/102": "/MOCK_JSON/thread-opshti-diskusii-102-mock.json",
};

export async function getThread(forumSlug, threadId) {
  if (USE_MOCK) {
    const url = MOCK_THREADS[`${forumSlug}/${threadId}`];
    if (!url) return null;
    const res = await fetch(url);
    if (res.status === 404) return null;
    if (!res.ok) throw new Error(`Failed to load thread: ${res.status}`);
    const { data } = await res.json();
    return data;
  }

  const res = await apiFetch(`/api/p/${forumSlug}/comments/${threadId}`);

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
 *   poll?: { question: string, options: string[] } | null,
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
