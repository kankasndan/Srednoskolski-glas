"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faLink, faXmark } from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;

// Prepoznava samo vgradeni (embed) linkovi od YouTube i TikTok - normalen
// share/watch link vrakja null pa go odbivame.
function parseEmbed(input) {
  const text = input.trim();

  const youtube = text.match(/youtube(?:-nocookie)?\.com\/embed\/([\w-]{11})/);
  if (youtube) return { type: "youtube", id: youtube[1] };

  const isTikTokEmbed =
    /tiktok-embed|tiktok\.com\/(?:embed|player)/.test(text) ||
    (/data-video-id=/.test(text) && /tiktok\.com/.test(text));
  if (isTikTokEmbed) {
    const id =
      text.match(/data-video-id="(\d+)"/)?.[1] ??
      text.match(/tiktok\.com\/(?:embed\/v2\/|player\/v1\/)(\d+)/)?.[1] ??
      text.match(/tiktok\.com\/@[\w.-]+\/video\/(\d+)/)?.[1] ??
      null;
    if (id) return { type: "tiktok", id };
  }

  return null;
}

function EmbedPreview({ embed, onClose }) {
  const frameClassName =
    embed.type === "youtube"
      ? "aspect-video w-full overflow-hidden rounded-2xl bg-black"
      : "mx-auto aspect-[9/16] w-full max-w-72 overflow-hidden rounded-2xl bg-black";

  const src =
    embed.type === "youtube"
      ? `https://www.youtube.com/embed/${embed.id}`
      : `https://www.tiktok.com/player/v1/${embed.id}?description=0&music_info=0&rel=0`;

  return (
    <div className="relative">
      <div className={frameClassName}>
        <iframe
          src={src}
          title={embed.type === "youtube" ? "YouTube video" : "TikTok video"}
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
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

  if (embed) return <EmbedPreview embed={embed} onClose={onClose} />;

  return (
    <div className="flex flex-col gap-3">
      <div className="flex items-center gap-3 rounded-xl border border-[#CCCCCC] bg-white px-4 py-3">
        <FontAwesomeIcon icon={faLink} className="h-5 w-5 text-[#595959]" />
        <input
          type="text"
          value={value}
          onChange={(event) => onChange(event.target.value)}
          placeholder="Залепете вграден (embed) линк"
          className="flex-1 font-[family-name:var(--font-manrope)] text-[14px] text-black outline-none placeholder:text-[#595959]"
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
