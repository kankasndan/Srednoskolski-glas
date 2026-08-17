"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft } from "@fortawesome/free-solid-svg-icons";
import { useRouter } from "next/navigation";
import { hasNavigatedInApp } from "@/lib/navHistory";

config.autoAddCss = false;

const LABEL_TONES = {
  primary: "text-[#582FF5]",
  muted: "text-[#595959]",
};

export default function BackButton({
  href,
  label = "Назад кон почетна",
  tone = "primary",
  className = "",
  iconClassName = "h-4",
}) {
  const router = useRouter();

  function handleBack() {
    if (href) {
      router.push(href);
      return;
    }
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
      className={`inline-flex h-10 shrink-0 cursor-pointer items-center self-start rounded-full font-[family-name:var(--font-manrope)] text-[14px] leading-none font-medium transition-colors hover:text-black ${
        label ? "gap-2" : "w-10 justify-center"
      } ${LABEL_TONES[tone]} ${className}`}
    >
      <FontAwesomeIcon icon={faChevronLeft} className={iconClassName} />
      {label}
    </button>
  );
}
