import Image from "next/image";

function CheckBox({ selected }) {
  return (
    <span
      className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-md ${
        selected ? "bg-white" : "border border-[#582FF5]/40"
      }`}
    >
      {selected && (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
          <path
            d="M20 6 9 17l-5-5"
            stroke="#582FF5"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      )}
    </span>
  );
}

function PlusBox() {
  return (
    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#582FF5]">
      <Image src="/plus.svg" alt="" width={16} height={16} />
    </span>
  );
}

export default function NavItem({ label, selected = false, variant = "check", onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={`flex h-12 w-[268px] cursor-pointer items-center gap-3 rounded-2xl px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-medium transition-colors ${
        selected
          ? "bg-[#582FF5] text-white"
          : "border border-[#582FF5]/30 text-[#595959]"
      }`}
    >
      {variant === "plus" ? <PlusBox /> : <CheckBox selected={selected} />}
      <span>{label}</span>
    </button>
  );
}
