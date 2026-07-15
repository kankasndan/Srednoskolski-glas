"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";

// Guards the onboarding step: it may only be reached right after completing the
// registration OAuth flow, which sets the "onboarding_pending" flag (see the
// auth callback). A direct visit, a refresh after finishing, or a user who
// signed in via /login won't have the flag and is sent back to /register.
export default function OnboardingGuard({ children }) {
  const router = useRouter();
  const [allowed, setAllowed] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem("auth_token");
    const pending = localStorage.getItem("onboarding_pending");

    if (token && pending) {
      setAllowed(true);
      return;
    }

    router.replace("/register");
  }, [router]);

  if (!allowed) return null; // render nothing until the check passes / redirect fires
  return children;
}
