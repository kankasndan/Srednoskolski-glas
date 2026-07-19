export default function PillButton({ label, selected, onClick, leading, className = "" }) {
  return (
    <button
      type="button"
      aria-pressed={selected}
      onClick={onClick}
      className={`flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl border px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 transition-colors ${
        selected
          ? "border-[#582FF5] bg-[#CFE9ED] text-black"
          : "border-[#CCCCCC] bg-white text-[#595959] hover:bg-[#DCEBED] hover:text-black"
      } ${className}`}
    >
      {leading}
      {label}
    </button>
  );
}
