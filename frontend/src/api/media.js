import Cookies from "js-cookie";
import { API_BASE_URL, ensureCsrfCookie } from "@/lib/api";

/**
 * @param {File} file
 * @param {string} [directory]
 */
export async function uploadMedia(file, directory = "avatars") {
  await ensureCsrfCookie();

  const formData = new FormData();
  formData.append("file", file);
  formData.append("directory", directory);

  const response = await fetch(`${API_BASE_URL}/api/media`, {
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
    const error = new Error(body.message || `Failed to upload file (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body;
}
