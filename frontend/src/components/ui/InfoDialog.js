import DialogShell from "@/components/ui/DialogShell";

// Porakata se prekrshuva na razlichno mesto vo sekoj dizajn, pa shirinata doagja odnadvor.
export default function InfoDialog({
  open,
  title,
  message,
  note,
  messageWidthClassName = "max-w-[330px]",
  onClose,
}) {
  return (
    <DialogShell
      open={open}
      label={title}
      onClose={onClose}
      widthClassName="max-w-[400px]"
      autoHeight={Boolean(note)}
    >
      <div className="flex flex-col items-center gap-3 text-center">
        <p className="max-w-[288px] font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-snug text-[var(--color-primary-200)]">
          {title}
        </p>
        {message && (
          <p
            className={`font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-snug text-black ${messageWidthClassName}`}
          >
            {message}
          </p>
        )}
        {note && (
          <p className="max-w-[304px] font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-snug text-[#595959]">
            {note}
          </p>
        )}
      </div>
    </DialogShell>
  );
}
