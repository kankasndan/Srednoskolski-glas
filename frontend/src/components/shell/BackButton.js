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
      className={`h-stack h-10 cursor-pointer items-center rounded-full font-[family-name:var(--font-manrope)] text-[14px] leading-none font-medium transition-colors hover:text-black ${
        label ? "gap-2" : "w-10 justify-center"
      } ${LABEL_TONES[tone]}`}
    >
      <FontAwesomeIcon icon={faChevronLeft} className="h-4" />
      {label}
    </button>
  );
}
