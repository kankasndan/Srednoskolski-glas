"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import ToastMessage from "@/components/ui/ToastMessage";

const SUCCESS_MESSAGE = "Линкот до дискусијата е успешно копиран.";
const ERROR_MESSAGE = "Линкот не успеа да се копира. Обиди се повторно.";

function copyWithTextarea(text) {
  const textarea = document.createElement("textarea");

  textarea.value = text;
  textarea.setAttribute("readonly", "");
  textarea.style.position = "fixed";
  textarea.style.top = "-9999px";
  document.body.appendChild(textarea);
  textarea.select();

  const copied = document.execCommand("copy");
  document.body.removeChild(textarea);

  if (!copied) {
    throw new Error("Copy failed");
  }
}

export default function ThreadShareButton({
  className = "",
  getUrl,
  successMessage = SUCCESS_MESSAGE,
  errorMessage = ERROR_MESSAGE,
}) {
  const [toast, setToast] = useState(null);

  useEffect(() => {
    if (!toast) return undefined;

    const timeoutId = window.setTimeout(() => {
      setToast(null);
    }, 3000);

    return () => window.clearTimeout(timeoutId);
  }, [toast]);

  async function copyCurrentUrl() {
    const url = getUrl ? getUrl() : window.location.href;

    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(url);
      } else {
        copyWithTextarea(url);
      }

      setToast({ message: successMessage, tone: "success" });
    } catch {
      setToast({ message: errorMessage, tone: "error" });
    }
  }

  return (
    <>
      <button
        type="button"
        aria-label="Сподели"
        onClick={copyCurrentUrl}
        className={`grid size-9 shrink-0 cursor-pointer place-items-center rounded-lg text-[#333333] transition-colors hover:bg-[#E5E5E5] ${className}`}
      >
        <Image
          src="/share-line.svg"
          alt=""
          width={18}
          height={18}
          className="size-[18px]"
        />
      </button>

      <ToastMessage
        message={toast?.message}
        tone={toast?.tone}
        onClose={() => setToast(null)}
      />
    </>
  );
}
