export default function PillButton({
  label,
  selected,
  onClick,
  leading,
  className = "",
  disabled = false,
  disabledMessage,
}) {
  const button = (
    <button
      type="button"
      aria-pressed={selected}
      disabled={disabled}
      onClick={onClick}
      className={`flex h-10 w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl border px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 transition-colors ${
        disabled
          ? "cursor-not-allowed border-[#CCCCCC] bg-[#F5F5F5] text-[#B3B3B3]"
          : selected
            ? "border-[#582FF5] bg-[#CFE9ED] text-black"
            : "border-[#CCCCCC] bg-white text-[#595959] hover:bg-[#DCEBED] hover:text-black"
      }`}
    >
      {leading}
      {label}
    </button>
  );

  if (!disabled || !disabledMessage) {
    return <div className={className}>{button}</div>;
  }

  return (
    <div className={`group relative ${className}`}>
      {button}
      <div
        role="tooltip"
        className="pointer-events-none absolute left-0 top-full z-20 mt-2 w-full rounded-xl border border-[#CCCCCC] bg-white p-3 text-[13px] font-normal leading-5 text-[#595959] opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
      >
        {disabledMessage}
      </div>
    </div>
  );
}
