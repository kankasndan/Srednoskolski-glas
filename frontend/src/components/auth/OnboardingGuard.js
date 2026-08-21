"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { getProfileUser } from "@/api/profile";
import { hasCompletedOnboarding, needsOnboarding } from "@/lib/capabilities";

// Allows the onboarding flow when:
// - OAuth callback just set onboarding_pending, or
// - the signed-in user still has incomplete onboarding (resume from home CTA).
export default function OnboardingGuard({ children }) {
  const router = useRouter();
  const [allowed, setAllowed] = useState(false);

  useEffect(() => {
    let cancelled = false;

    async function check() {
      const pending = localStorage.getItem("onboarding_pending");

      if (pending) {
        if (!cancelled) setAllowed(true);
        return;
      }

      try {
        const user = await getProfileUser();
        if (cancelled) return;

        if (needsOnboarding(user)) {
          localStorage.setItem("onboarding_pending", "1");
          setAllowed(true);
          return;
        }

        if (hasCompletedOnboarding(user)) {
          router.replace("/feed");
          return;
        }
      } catch {
        // Not signed in — fall through to register.
      }

      if (!cancelled) {
        router.replace("/register");
      }
    }

    check();

    return () => {
      cancelled = true;
    };
  }, [router]);

  if (!allowed) return null;
  return children;
}
