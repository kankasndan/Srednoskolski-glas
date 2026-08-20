import DialogShell from "@/components/ui/DialogShell";
import PrimaryButton from "@/components/ui/PrimaryButton";

export default function ConfirmDialog({
  open,
  title,
  confirmLabel,
  cancelLabel = "Откажи",
  onConfirm,
  onCancel,
}) {
  return (
    <DialogShell open={open} label={title} onClose={onCancel} widthClassName="max-w-[400px]">
      <div className="flex flex-col items-center justify-center gap-6">
        <p className="max-w-[285px] text-center font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-snug text-black">
          {title}
        </p>

        <div className="flex gap-3">
          <button
            type="button"
            onClick={onCancel}
            className="h-10 w-36 cursor-pointer rounded-xl border border-[var(--color-primary-200)] bg-white font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#F1EEFE] active:bg-[var(--color-primary-200)] active:text-white md:active:bg-[#F1EEFE] md:active:text-black"
          >
            {cancelLabel}
          </button>
          <PrimaryButton
            type="button"
            autoFocus
            onClick={onConfirm}
            className="h-10 w-36 font-[family-name:var(--font-manrope)] text-[14px] leading-none"
          >
            {confirmLabel}
          </PrimaryButton>
        </div>
      </div>
    </DialogShell>
  );
}
