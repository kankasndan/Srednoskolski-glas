"use client";

import { useEffect } from "react";

// Zatvora dropdown ili meni koga se klika nadvor od nego ili se pritisne Escape.
export function useClickOutside(ref, onOutside, enabled = true) {
  useEffect(() => {
    if (!enabled) return;

    function handlePointerDown(event) {
      if (ref.current && !ref.current.contains(event.target)) onOutside();
    }

    function handleKeyDown(event) {
      if (event.key === "Escape") onOutside();
    }

    document.addEventListener("mousedown", handlePointerDown);
    document.addEventListener("keydown", handleKeyDown);

    return () => {
      document.removeEventListener("mousedown", handlePointerDown);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [ref, onOutside, enabled]);
}
