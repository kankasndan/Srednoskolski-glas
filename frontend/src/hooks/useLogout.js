"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { apiFetch } from "@/lib/api";

// Odjavata se povikuva od hederot i od profilot, pa celata sostojba e tuka.
export function useLogout({ onLoggedOut } = {}) {
  const router = useRouter();
  const [confirming, setConfirming] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const [loggedOut, setLoggedOut] = useState(false);

  async function logout() {
    setConfirming(false);
    setLoggingOut(true);

    try {
      await apiFetch("/api/logout", { method: "POST" });
    } catch {
      // I pri neuspeh chistime lokalno za da ne izgleda najaven; kolachot
      // sepak istekuva.
    } finally {
      localStorage.removeItem("onboarding_pending");
      setLoggingOut(false);
      setLoggedOut(true);
      onLoggedOut?.();
    }
  }

  // AppShell se remontira pri promena na strana, pa dijalogot bi ischeznal ako
  // odime na /feed vednash po odjavata.
  function finish() {
    setLoggedOut(false);
    router.replace("/feed");
  }

  return {
    confirming,
    loggingOut,
    loggedOut,
    ask: () => setConfirming(true),
    cancel: () => setConfirming(false),
    logout,
    finish,
  };
}
