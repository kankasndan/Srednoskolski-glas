import Cookies from "js-cookie";
import { API_BASE_URL, ensureCsrfCookie } from "@/lib/api";

/**
 * Sends the onboarding photo to Gemini and stores the generated avatar.
 *
 * @param {File} file
 */
export async function generateOnboardingAvatar(file) {
  await ensureCsrfCookie();

  const formData = new FormData();
  formData.append("file", file);

  const response = await fetch(`${API_BASE_URL}/api/onboarding/avatar`, {
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
    const firstError = body.errors ? Object.values(body.errors).flat()[0] : null;
    const error = new Error(
      firstError || body.message || `Failed to generate avatar (${response.status})`,
    );
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return body;
}
