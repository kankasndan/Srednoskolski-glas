"use client";

import DialogShell from "@/components/ui/DialogShell";
import PrimaryButton from "@/components/ui/PrimaryButton";

function contentLabel(content) {
  if (content?.type === "thread") return "Твојата дискусија";
  if (content?.type === "comment") return "Твојот коментар";
  return null;
}

function hasPostedContent(content) {
  if (!content) return false;
  return Boolean(content.title || content.body || content.gif_url);
}

export default function SanctionReasonDialog({
  open,
  reason,
  content,
  canAppeal = false,
  onAppeal,
  onClose,
}) {
  const postedLabel = contentLabel(content);
  const showPost = hasPostedContent(content);

  return (
    <DialogShell
      open={open}
      label="Причина за санкцијата"
      onClose={onClose}
      widthClassName="max-w-[400px]"
      autoHeight
    >
      <div className="flex w-full flex-col items-center gap-6 text-center">
        <div className="flex w-full flex-col items-center gap-3">
          <p className="max-w-[288px] font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-snug text-[var(--color-primary-200)]">
            Причина за санкцијата
          </p>
          <p className="max-w-[324px] whitespace-pre-wrap font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-snug text-black">
            {reason || "Нема наведена причина."}
          </p>
        </div>

        {showPost ? (
          <div className="flex w-full flex-col items-center gap-2 text-left">
            {postedLabel ? (
              <p className="w-full font-[family-name:var(--font-manrope)] text-[12px] font-bold leading-none text-[#595959]">
                {postedLabel}
              </p>
            ) : null}
            {content.title ? (
              <p className="w-full font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-snug text-black">
                {content.title}
              </p>
            ) : null}
            {content.body ? (
              <p className="w-full whitespace-pre-wrap font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-snug text-black">
                {content.body}
              </p>
            ) : null}
            {content.gif_url ? (
              <img src={content.gif_url} alt="GIF" className="max-h-40 max-w-full rounded-xl" />
            ) : null}
          </div>
        ) : null}

        {canAppeal && onAppeal ? (
          <PrimaryButton
            type="button"
            onClick={onAppeal}
            className="h-10 w-full max-w-[200px] font-[family-name:var(--font-manrope)] text-[14px] leading-none"
          >
            Жалба
          </PrimaryButton>
        ) : null}
      </div>
    </DialogShell>
  );
}
