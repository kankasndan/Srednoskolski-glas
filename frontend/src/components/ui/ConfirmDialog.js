import DialogShell from "@/components/ui/DialogShell";

export default function ConfirmDialog({
  open,
  title,
  confirmLabel,
  cancelLabel = "Откажи",
  onConfirm,
  onCancel,
}) {
  return (
    <DialogShell open={open} label={title} onClose={onCancel}>
      <p className="text-center font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-6 text-black">
        {title}
      </p>

      <div className="flex w-full gap-3">
        <button
          type="button"
          onClick={onCancel}
          className="flex-1 cursor-pointer rounded-xl border-[0.5px] border-[var(--color-primary-200)] bg-white px-4 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#DCEBED]"
        >
          {cancelLabel}
        </button>
        <button
          type="button"
          autoFocus
          onClick={onConfirm}
          className="flex-1 cursor-pointer rounded-xl bg-[var(--color-primary-200)] px-4 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#3300F5]"
        >
          {confirmLabel}
        </button>
      </div>
    </DialogShell>
  );
}
