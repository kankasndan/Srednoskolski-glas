"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

export default function AuthCallbackPage() {
  const router = useRouter();

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get("token");
    const onboarding = params.get("onboarding");

    const successRedirect =
      localStorage.getItem("post_login_redirect") || "/register/onboarding";
    const errorRedirect =
      localStorage.getItem("post_login_error_redirect") || "/register";
    localStorage.removeItem("post_login_redirect");
    localStorage.removeItem("post_login_error_redirect");

    if (token) {
      localStorage.setItem("auth_token", token);
      const onboardingRequired = onboarding === "required";

      if (onboardingRequired) {
        localStorage.setItem("onboarding_pending", "1");
      } else {
        localStorage.removeItem("onboarding_pending");
      }

      const nextPath = onboardingRequired
        ? "/register/onboarding"
        : successRedirect === "/register/onboarding"
          ? "/feed"
          : successRedirect;

      router.replace(nextPath);
      return;
    }

    router.replace(`${errorRedirect}?error=missing_token`);
  }, [router]);

  return (
    <main className="box-border min-h-screen w-full p-6 flex items-center justify-center">
      <p>Signing you in...</p>
    </main>
  );
}