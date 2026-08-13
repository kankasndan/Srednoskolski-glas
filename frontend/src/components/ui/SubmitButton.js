export default function SubmitButton({ label, disabled, disabledTooltip }) {
  return (
    <div className="group relative">
      {disabled && disabledTooltip && (
        <div className="pointer-events-none absolute -top-11 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-[#0A0A0A] px-3 py-2 text-center font-(family-name:--font-manrope) text-[12px] text-white opacity-0 transition-opacity group-hover:opacity-100">
          {disabledTooltip}
          <div className="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#0A0A0A]" />
        </div>
      )}
      <button
        type="submit"
        disabled={disabled}
        className="h-14 w-full cursor-pointer rounded-[16px] bg-[#582FF5] font-(family-name:--font-manrope) text-[16px] font-bold text-white transition-colors hover:bg-[#4B25E0] disabled:cursor-not-allowed disabled:bg-[#CCCCCC] disabled:text-white disabled:hover:bg-[#CCCCCC] lg:h-12 2xl:h-14 2xl:text-[18px]"
      >
        {label}
      </button>
    </div>
  );
}
