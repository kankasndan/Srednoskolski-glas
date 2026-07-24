import FieldLabel from "@/components/FieldLabel";

const MAX_LENGTH = 100;

export default function TitleInput({ value, onChange, onBlur, errorMessage }) {
  const counterTextColor =
    value.length >= MAX_LENGTH ? "text-[var(--color-error)]" : "text-[#595959]";

  return (
    <div className="flex flex-col gap-2">
      <FieldLabel htmlFor="title" required>
        Наслов
      </FieldLabel>
      <div className="flex w-[632px] max-w-full flex-col gap-1">
        <input
          id="title"
          name="title"
          type="text"
          required
          maxLength={MAX_LENGTH}
          value={value}
          aria-invalid={!!errorMessage}
          aria-describedby={errorMessage ? "title-error" : undefined}
          onChange={(event) => onChange(event.target.value)}
          onBlur={onBlur}
          onInvalid={(event) =>
            event.target.setCustomValidity("Внеси барем 1 карактер.")
          }
          onInput={(event) => event.target.setCustomValidity("")}
          placeholder="Внеси наслов на дискусијата"
          className={`h-10 w-full rounded-xl border px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 text-black placeholder:text-[#595959] focus:outline-none ${
            errorMessage ? "border-[var(--color-error)]" : "border-[#CCCCCC] focus:border-[#582FF5]"
          }`}
        />
        <div className="flex min-h-4 items-center justify-between gap-3">
          <p
            id="title-error"
            className={`min-w-0 flex-1 truncate font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)] ${
              errorMessage ? "" : "invisible"
            }`}
          >
            {errorMessage || "Нема грешка"}
          </p>
          <span
            className={`shrink-0 font-[family-name:var(--font-manrope)] text-[12px] leading-none ${counterTextColor}`}
          >
            {value.length}/{MAX_LENGTH}
          </span>
        </div>
      </div>
    </div>
  );
}
