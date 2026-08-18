"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faLink, faXmark } from "@fortawesome/free-solid-svg-icons";
import { parseEmbed, toEmbedUrl } from "@/lib/embeds";

config.autoAddCss = false;

function EmbedPreview({ embed, onClose }) {
  const frameClassName =
    embed.type === "youtube"
      ? "aspect-video w-full overflow-hidden rounded-2xl bg-black"
      : "mx-auto aspect-[9/16] w-full max-w-72 overflow-hidden rounded-2xl bg-black";

  const src = toEmbedUrl(embed);

  return (
    <div className="relative">
      <div className={frameClassName}>
        <iframe
          src={src}
          title={embed.type === "youtube" ? "YouTube video" : "TikTok video"}
          allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"
          sandbox="allow-scripts allow-same-origin allow-presentation"
          allowFullScreen
          className="size-full"
        />
      </div>
      <button
        type="button"
        aria-label="Отстрани го линкот"
        onClick={onClose}
        className="absolute right-2 top-2 z-10 flex size-8 cursor-pointer items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
      >
        <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
      </button>
    </div>
  );
}

export default function LinkAttachment({ value, onChange, onClose }) {
  const trimmedValue = value.trim();
  const embed = trimmedValue ? parseEmbed(trimmedValue) : null;
  const showError = Boolean(trimmedValue) && !embed;

  function handleChange(next) {
    const parsed = parseEmbed(next);
    // Store a real URL so Laravel's `url` rule accepts the field.
    onChange(parsed ? toEmbedUrl(parsed) : next);
  }

  if (embed) {
    return (
      <div className="max-w-full min-w-0">
        <EmbedPreview embed={embed} onClose={onClose} />
      </div>
    );
  }

  return (
    <div className="flex max-w-full min-w-0 flex-col gap-3">
      <div className="flex min-w-0 items-center gap-3 rounded-xl border border-[#CCCCCC] bg-white px-4 py-3">
        <FontAwesomeIcon icon={faLink} className="h-5 w-5 shrink-0 text-[#595959]" />
        <input
          type="text"
          value={value}
          onChange={(event) => handleChange(event.target.value)}
          placeholder="Залепете вграден (embed) линк"
          className="min-w-0 flex-1 font-[family-name:var(--font-manrope)] text-[14px] text-black outline-none placeholder:text-[#595959]"
        />
        <button
          type="button"
          aria-label="Отстрани го линкот"
          onClick={onClose}
          className="flex size-6 shrink-0 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>
      </div>

      {showError && (
        <p className="font-[family-name:var(--font-manrope)] text-[12px] text-[var(--color-error)]">
          Ве молиме залепете вграден (embed) линк од YouTube или TikTok.
        </p>
      )}
    </div>
  );
}
