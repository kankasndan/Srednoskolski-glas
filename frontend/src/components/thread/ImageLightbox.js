"use client";

import { useEffect, useState } from "react";
import { createPortal } from "react-dom";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faDownload, faXmark } from "@fortawesome/free-solid-svg-icons";

function fileNameFromUrl(url) {
  try {
    const path = new URL(url).pathname;
    const name = decodeURIComponent(path.split("/").filter(Boolean).pop() || "slika");
    return name.includes(".") ? name : `${name}.jpg`;
  } catch {
    return "slika.jpg";
  }
}

async function downloadImage(url) {
  const filename = fileNameFromUrl(url);

  try {
    const response = await fetch(url, { mode: "cors" });
    if (!response.ok) throw new Error("download failed");

    const blob = await response.blob();
    const objectUrl = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = objectUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(objectUrl);
  } catch {
    // Cross-origin hosts that block CORS still open the image for save-as.
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.target = "_blank";
    link.rel = "noopener noreferrer";
    document.body.appendChild(link);
    link.click();
    link.remove();
  }
}

export default function ImageLightbox({ src, onClose }) {
  const [downloading, setDownloading] = useState(false);

  useEffect(() => {
    function handleKeyDown(event) {
      if (event.key === "Escape") onClose?.();
    }

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, [onClose]);

  async function handleDownload(event) {
    event.stopPropagation();
    if (downloading || !src) return;

    setDownloading(true);
    try {
      await downloadImage(src);
    } finally {
      setDownloading(false);
    }
  }

  if (typeof document === "undefined") return null;

  // Portal to body so feed/profile stacking contexts (e.g. relative z-10)
  // cannot trap this overlay under the sticky header (z-50).
  return createPortal(
    <div
      role="dialog"
      aria-modal="true"
      aria-label="Преглед на слика"
      className="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
      onClick={onClose}
    >
      <div className="absolute right-4 top-4 z-10 flex items-center gap-2">
        <button
          type="button"
          onClick={handleDownload}
          disabled={downloading}
          aria-label="Преземи слика"
          className="flex size-11 cursor-pointer items-center justify-center rounded-full bg-white/15 text-white transition-colors hover:bg-white/25 disabled:opacity-60"
        >
          <FontAwesomeIcon icon={faDownload} className="h-5 w-5" />
        </button>
        <button
          type="button"
          onClick={(event) => {
            event.stopPropagation();
            onClose?.();
          }}
          aria-label="Затвори"
          className="flex size-11 cursor-pointer items-center justify-center rounded-full bg-white/15 text-white transition-colors hover:bg-white/25"
        >
          <FontAwesomeIcon icon={faXmark} className="h-5 w-5" />
        </button>
      </div>

      <img
        src={src}
        alt=""
        onClick={(event) => event.stopPropagation()}
        className="max-h-full max-w-full object-contain"
      />
    </div>,
    document.body,
  );
}
