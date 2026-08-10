"use client";

import { useEffect, useRef } from "react";
import { usePathname } from "next/navigation";
import { markInAppNavigation } from "@/lib/navHistory";

// Renders nothing. Watches the pathname and, after the initial load, marks that
// an in-app navigation has occurred so auth pages know a back() will stay in-app.
export default function NavigationTracker() {
  const pathname = usePathname();
  const isInitialLoad = useRef(true);

  useEffect(() => {
    if (isInitialLoad.current) {
      isInitialLoad.current = false; // the first render is the landing page, not a navigation
      return;
    }
    markInAppNavigation();
  }, [pathname]);

  return null;
}
