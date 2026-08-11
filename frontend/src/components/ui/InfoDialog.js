import DialogShell from "@/components/ui/DialogShell";

export default function InfoDialog({ open, title, message, onClose }) {
  return (
    <DialogShell open={open} label={title} onClose={onClose}>
      <div className="flex flex-col items-center gap-3 text-center">
        <p className="font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-6 text-[var(--color-primary-200)]">
          {title}
        </p>
        {message && (
          <p className="font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 text-black">
            {message}
          </p>
        )}
      </div>
    </DialogShell>
  );
}
