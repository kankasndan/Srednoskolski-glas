"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft } from "@fortawesome/free-solid-svg-icons";
import { useRouter } from "next/navigation";

config.autoAddCss = false;

export default function LoginBackButton() {
  const router = useRouter();

  return (
    <button
      type="button"
      onClick={() => router.back()}
      aria-label="Назад"
      className="flex size-10 items-center justify-center rounded-full text-[#0A0A0A] transition-colors hover:bg-gray-100"
    >
      <FontAwesomeIcon icon={faChevronLeft} className="h-4" />
    </button>
  );
}
