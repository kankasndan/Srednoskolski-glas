"use client";

import DialogShell from "@/components/ui/DialogShell";
import PrimaryButton from "@/components/ui/PrimaryButton";
import { timedBanPopupMessage } from "@/lib/ban";

const SANCTIONS = {
  warning: {
    title: "Доби предупредување.",
    message:
      "Твојата содржина ги прекрши правилата на заедницата. Те молиме внимавај при следното објавување, повторно прекршување ќе резултира со привремен бан.",
  },
  "7-day": {
    title: "Имаш привремен бан.",
  },
  custom: {
    title: "Имаш привремен бан.",
  },
  permanent_ban: {
    title: "Твојот профил е трајно баниран.",
    message:
      "Поради повторени прекршувања на правилата, твојот профил е трајно баниран и повеќе не можеш да учествуваш на Средношколски Глас.",
  },
  ban_ended: {
    title: "Банот заврши!",
    message:
      "Твојот бан истече и повторно можеш да објавуваш дискусии и да коментираш.",
    note: "Те молиме почитувај ги правилата и придонесувај кон позитивна и безбедна заедница.",
  },
};

function copyFor(type, expiresAt) {
  const sanction = SANCTIONS[type];
  if (!sanction) return null;

  if (type === "7-day" || type === "custom") {
    return {
      ...sanction,
      message: timedBanPopupMessage(expiresAt),
    };
  }

  return sanction;
}

export function DialogActionButton({ children, onClick, className = "" }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={`h-10 min-w-0 flex-1 cursor-pointer rounded-xl border border-[var(--color-primary-200)] bg-white px-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#F1EEFE] active:bg-[var(--color-primary-200)] active:text-white md:active:bg-[#F1EEFE] md:active:text-black ${className}`}
    >
      {children}
    </button>
  );
}

// Pop-up za sankcii. Tipot i expires_at doagjaat od /api/me.
export default function SanctionDialog({
  open,
  type,
  expiresAt,
  onClose,
  onSeeReason,
  onAppeal,
}) {
  const sanction = copyFor(type, expiresAt);

  if (!sanction) return null;

  const showActions = type !== "ban_ended" && (onSeeReason || onAppeal);

  return (
    <DialogShell
      open={open}
      label={sanction.title}
      onClose={onClose}
      widthClassName="max-w-[400px]"
      autoHeight
    >
      <div className="flex flex-col items-center gap-6 text-center">
        <div className="flex flex-col items-center gap-3">
          <p className="max-w-[288px] font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-snug text-[var(--color-primary-200)]">
            {sanction.title}
          </p>
          {sanction.message ? (
            <p className="max-w-[324px] font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-snug text-black">
              {sanction.message}
            </p>
          ) : null}
          {sanction.note ? (
            <p className="max-w-[304px] font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-snug text-[#595959]">
              {sanction.note}
            </p>
          ) : null}
        </div>

        {showActions ? (
          <div className="flex w-full gap-3">
            {onSeeReason ? (
              <DialogActionButton onClick={onSeeReason}>Види причина</DialogActionButton>
            ) : null}
            {onAppeal ? (
              <PrimaryButton
                type="button"
                onClick={onAppeal}
                className="h-10 min-w-0 flex-1 px-3 font-[family-name:var(--font-manrope)] text-[14px] leading-none"
              >
                Жалба
              </PrimaryButton>
            ) : null}
          </div>
        ) : null}
      </div>
    </DialogShell>
  );
}
