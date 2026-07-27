"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft } from "@fortawesome/free-solid-svg-icons";
import { useRouter } from "next/navigation";
import { hasNavigatedInApp } from "@/lib/navHistory";

config.autoAddCss = false;

const LABEL_TONES = {
  primary: "text-[14px] font-medium text-[#582FF5]",
  muted: "text-[16px] text-[#595959]",
};

export default function BackButton({ href, label, tone = "primary" }) {
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
      aria-label={label ? undefined : "Назад"}
      className={`flex h-10 cursor-pointer items-center justify-center rounded-full transition-colors ${
        label
          ? `gap-2 font-[family-name:var(--font-manrope)] leading-none hover:text-black ${LABEL_TONES[tone]}`
          : "w-10 text-[#0A0A0A] hover:bg-gray-100"
      }`}
    >
      <FontAwesomeIcon icon={faChevronLeft} className="h-4" />
      {label}
    </button>
  );
}
