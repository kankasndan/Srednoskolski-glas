// Shared styling for onboarding form fields (label + input/select boxes).
// 2xl: variants (≥1536px) scale everything up on large desktop monitors so the
// form doesn't look tiny in the wide right-hand column.
export const labelClass =
  "font-(family-name:--font-manrope) text-[16px] font-normal leading-[14px] text-[#000000] 2xl:text-[18px]";

const fieldBase =
  "w-full border border-[#CCCCCC] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-normal leading-none focus:border-[#582FF5] focus:outline-none 2xl:px-5 2xl:text-[16px]";

export const fieldClass = `${fieldBase} h-14 rounded-2xl 2xl:h-14`;

// Poniskata varijanta odi so poliwata na uredi profil.
export const compactFieldClass = `${fieldBase} h-10 rounded-xl md:h-12`;
