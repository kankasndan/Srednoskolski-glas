"use client";

import { useRef, useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft, faChevronRight, faLink } from "@fortawesome/free-solid-svg-icons";
import ImageLightbox from "@/components/thread/ImageLightbox";
import { parseEmbed as parseEmbedPaste, toEmbedUrl } from "@/lib/embeds";
import { safeExternalUrl } from "@/lib/paths";

config.autoAddCss = false;

function fileNameFromUrl(url) {
  try {
    const path = new URL(url).pathname;
    const name = decodeURIComponent(path.split("/").filter(Boolean).pop() || "датотека");
    return name || "датотека";
  } catch {
    return "датотека";
  }
}

function fileExt(url) {
  const name = fileNameFromUrl(url);
  const parts = name.split(".");
  return parts.length > 1 ? parts.pop().toUpperCase() : "FILE";
}

function parseEmbed(input) {
  const fromPaste = parseEmbedPaste(input);
  if (fromPaste) return fromPaste;

  const text = (input || "").trim();
  const youtube = text.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/);
  if (youtube) return { type: "youtube", id: youtube[1] };

  const tiktok = text.match(/tiktok\.com\/@[\w.-]+\/video\/(\d+)/);
  if (tiktok) return { type: "tiktok", id: tiktok[1] };

  return null;
}

/** Scrollable gallery of images + videos, in the order they were attached. */
function MediaGallery({ items: rawItems }) {
  const trackRef = useRef(null);
  const [lightboxSrc, setLightboxSrc] = useState(null);

  const items = rawItems
    .map((item) => ({ ...item, url: safeExternalUrl(item.url) }))
    .filter((item) => item.url !== null);

  function scrollByFrame(direction) {
    const track = trackRef.current;
    if (!track) return;
    track.scrollBy({ left: direction * track.clientWidth, behavior: "smooth" });
  }

  if (!items.length) return null;

  return (
    <div className="relative">
      <div
        ref={trackRef}
        className="flex snap-x snap-mandatory gap-3 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      >
        {items.map((item) => (
          <div
            key={`${item.type}-${item.url}`}
            className="relative h-[191px] w-full shrink-0 snap-center overflow-hidden rounded-2xl bg-black md:aspect-[990/421] md:h-auto"
          >
            {item.type === "video" ? (
              <video
                src={item.url}
                controls
                preload="metadata"
                playsInline
                className="relative z-10 size-full object-contain"
              />
            ) : (
              <button
                type="button"
                aria-label="Отвори слика на цел екран"
                onClick={() => setLightboxSrc(item.url)}
                className="relative z-10 size-full cursor-zoom-in"
              >
                <img
                  src={item.url}
                  alt=""
                  aria-hidden="true"
                  className="absolute inset-0 size-full scale-110 object-cover blur-2xl"
                />
                <img src={item.url} alt="" className="relative z-10 size-full object-contain" />
              </button>
            )}
          </div>
        ))}
      </div>

      {items.length > 1 && (
        <>
          <button
            type="button"
            aria-label="Претходен прилог"
            onClick={() => scrollByFrame(-1)}
            className="absolute left-2 top-1/2 z-30 flex size-8 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
          >
            <FontAwesomeIcon icon={faChevronLeft} className="h-4 w-4" />
          </button>
          <button
            type="button"
            aria-label="Следен прилог"
            onClick={() => scrollByFrame(1)}
            className="absolute right-2 top-1/2 z-30 flex size-8 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
          >
            <FontAwesomeIcon icon={faChevronRight} className="h-4 w-4" />
          </button>
        </>
      )}

      {lightboxSrc ? (
        <ImageLightbox src={lightboxSrc} onClose={() => setLightboxSrc(null)} />
      ) : null}
    </div>
  );
}

function FileCard({ url: rawUrl }) {
  const url = safeExternalUrl(rawUrl);
  if (!url) return null;

  return (
    <a
      href={url}
      target="_blank"
      rel="noopener noreferrer"
      className="flex items-center gap-3 rounded-xl border border-[#CCCCCC] bg-white px-4 py-3 transition-colors hover:bg-[#F5F5F5]"
    >
      <img src="/new thread icons/documents.svg" alt="" className="h-5 w-auto" />
      <span className="flex min-w-0 flex-1 items-baseline gap-2">
        <span className="truncate font-[family-name:var(--font-manrope)] text-[14px] text-black">
          {fileNameFromUrl(url)}
        </span>
        <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
          {fileExt(url)}
        </span>
      </span>
    </a>
  );
}

function LinkDisplay({ url: rawUrl }) {
  const embed = parseEmbed(rawUrl);

  if (embed?.type === "youtube") {
    return (
      <div className="aspect-video w-full overflow-hidden rounded-2xl bg-black">
        <iframe
          src={toEmbedUrl(embed)}
          title="YouTube video"
          allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"
          sandbox="allow-scripts allow-same-origin allow-presentation"
          allowFullScreen
          className="size-full"
        />
      </div>
    );
  }

  if (embed?.type === "tiktok") {
    return (
      <div className="mx-auto aspect-[9/16] w-full max-w-72 overflow-hidden rounded-2xl bg-black">
        <iframe
          src={`${toEmbedUrl(embed)}?description=0&music_info=0&rel=0`}
          title="TikTok video"
          allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"
          sandbox="allow-scripts allow-same-origin allow-presentation"
          allowFullScreen
          className="size-full"
        />
      </div>
    );
  }

  const url = safeExternalUrl(rawUrl);
  if (!url) return null;

  return (
    <a
      href={url}
      target="_blank"
      rel="noopener noreferrer"
      className="flex items-center gap-3 rounded-xl border border-[#CCCCCC] bg-white px-4 py-3 transition-colors hover:bg-[#F5F5F5]"
    >
      <FontAwesomeIcon icon={faLink} className="h-5 w-5 text-[#595959]" />
      <span className="min-w-0 flex-1 truncate font-[family-name:var(--font-manrope)] text-[14px] text-[#582FF5]">
        {url}
      </span>
    </a>
  );
}

/**
 * Renders thread attachments by API `type` (from thread_attachments.slug):
 * image | video | file | link
 *
 * Images and videos share one carousel, preserving create order.
 */
export default function ThreadAttachments({ attachments = [] }) {
  if (!attachments.length) return null;

  const media = attachments.filter((a) => a.type === "image" || a.type === "video");
  const files = attachments.filter((a) => a.type === "file");
  const links = attachments.filter((a) => a.type === "link");

  return (
    <div className="flex flex-col gap-4">
      <MediaGallery items={media} />
      {files.map((file) => (
        <FileCard key={file.url} url={file.url} />
      ))}
      {links.map((link) => (
        <LinkDisplay key={link.url} url={link.url} />
      ))}
    </div>
  );
}
