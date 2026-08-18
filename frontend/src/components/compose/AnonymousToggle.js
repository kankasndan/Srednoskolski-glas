"use client";

import { useState } from "react";
import Checkbox from "@/components/ui/Checkbox";

export default function AnonymousToggle({ className = "", action, checked, onChange }) {
  const [anonymous, setAnonymous] = useState(false);
  const isControlled = checked !== undefined;
  const value = isControlled ? checked : anonymous;

  function handleChange(event) {
    const next = event.target.checked;
    if (!isControlled) setAnonymous(next);
    onChange?.(next);
  }

  return (
    <div className={`flex w-full flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-4 ${className}`}>
      <div className="flex min-w-0 flex-1 flex-col gap-2">
        <Checkbox checked={value} onChange={handleChange} className="w-fit">
          <span className="font-[family-name:var(--font-manrope)] text-[14px] text-black">
            Објави ја дискусијата анонимно
          </span>
        </Checkbox>
        <p className="w-full font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#595959]">
          Објавувањето на оваа дискусија анонимно значи дека твојот псевдоним нема да биде видлив на
          останатите корисници.
        </p>
      </div>
      {action ? <div className="w-full shrink-0 lg:w-auto">{action}</div> : null}
    </div>
  );
}
