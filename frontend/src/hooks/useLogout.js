"use client";

import { useState } from "react";
import { apiFetch } from "@/lib/api";

// Odjavata se povikuva od hederot i od profilot, pa celata sostojba e tuka.
export function useLogout({ onLoggedOut } = {}) {
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

  // Tvrda navigacija: /profile pri prazna sesija vodi na /login i bi ja
  // nadglasalo ova, a celosnoto vchituvanje go chisti i keshot na sesijata.
  function finish() {
    setLoggedOut(false);
    window.location.assign("/feed");
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
