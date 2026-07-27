"use client";

import { useRef } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronLeft, faChevronRight, faPlus, faXmark } from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;

const EMPTY = {
  image: { icon: "/new thread icons/photo.svg", label: "Прикачи слика тука", addLabel: "Додади слики" },
  video: { icon: "/new thread icons/video.svg", label: "Прикачи видео тука", addLabel: "Додади видеа" },
};

export default function MediaAttachments({ kind, items, max, onAdd, onRemove, onClose }) {
  const trackRef = useRef(null);
  const empty = EMPTY[kind];

  function scrollByFrame(direction) {
    const track = trackRef.current;
    if (!track) return;
    track.scrollBy({ left: direction * track.clientWidth, behavior: "smooth" });
  }

  return (
    <div className="flex flex-col gap-3 rounded-2xl border border-[#CCCCCC] bg-white p-4">
      <div className="flex items-center justify-between">
        <span className="font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
          {items.length}/{max}
        </span>
        <button
          type="button"
          aria-label="Отстрани ги прилозите"
          onClick={onClose}
          className="flex size-6 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>
      </div>

      {items.length === 0 ? (
        <button
          type="button"
          onClick={onAdd}
          className="flex w-full cursor-pointer flex-col items-center justify-center gap-2 py-10 text-[#595959]"
        >
          <img src={empty.icon} alt="" className="h-8 w-auto opacity-60" />
          <span className="font-[family-name:var(--font-manrope)] text-[14px]">{empty.label}</span>
        </button>
      ) : (
        <div className="flex flex-col gap-3">
          <div className="relative">
            <div
              ref={trackRef}
              className="flex snap-x snap-mandatory gap-3 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
              {items.map((item) => (
                <div
                  key={item.url}
                  className="relative aspect-[990/421] w-full shrink-0 snap-center overflow-hidden rounded-2xl bg-black"
                >
                  {kind === "video" ? (
                    <video
                      src={item.url}
                      controls
                      preload="metadata"
                      playsInline
                      className="relative z-10 size-full object-contain"
                    />
                  ) : (
                    <>
                      <img
                        src={item.url}
                        alt=""
                        aria-hidden="true"
                        className="absolute inset-0 size-full scale-110 object-cover blur-2xl"
                      />
                      <img src={item.url} alt="" className="relative z-10 size-full object-contain" />
                    </>
                  )}
                  <button
                    type="button"
                    aria-label="Отстрани го прилогот"
                    onClick={() => onRemove(item.url)}
                    className="absolute right-2 top-2 z-20 flex size-8 cursor-pointer items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
                  >
                    <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
                  </button>
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
          </div>

          {items.length < max && (
            <button
              type="button"
              onClick={onAdd}
              className="flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-[#CCCCCC] font-[family-name:var(--font-manrope)] text-[14px] text-[#595959] transition-colors hover:bg-[#DCEBED] hover:text-black"
            >
              <FontAwesomeIcon icon={faPlus} className="h-4 w-4" />
              {empty.addLabel}
            </button>
          )}
        </div>
      )}
    </div>
  );
}
