"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft } from "@fortawesome/free-solid-svg-icons";
import { useRouter } from "next/navigation";
import { hasNavigatedInApp } from "@/lib/navHistory";

config.autoAddCss = false;

export default function AuthBackButton({ href }) {
  const router = useRouter();

  function handleBack() {
    // Fixed destination (e.g. onboarding → /feed), so we don't step back onto a
    // page that no longer makes sense (like the login page after signing in).
    if (href) {
      router.push(href);
      return;
    }
    // Otherwise: came here from inside the app (a forum/thread) → step back one.
    // Landed here directly (typed URL / external link / new tab) → go to the feed
    // instead of leaving the site.
    if (hasNavigatedInApp()) {
      router.back();
    } else {
      router.push("/feed");
    }
  }

  return (
    <button
      type="button"
      onClick={handleBack}
      aria-label="Назад"
      className="flex size-10 items-center justify-center rounded-full text-[#0A0A0A] transition-colors hover:bg-gray-100"
    >
      <FontAwesomeIcon icon={faChevronLeft} className="h-4" />
    </button>
  );
}
