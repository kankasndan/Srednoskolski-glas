"use client";

import { useState } from "react";
import Checkbox from "@/components/Checkbox";

export default function AnonymousToggle({ className = "", action }) {
  const [anonymous, setAnonymous] = useState(false);

  return (
    <div className={`flex w-full flex-col gap-1 ${className}`}>
      <div className="flex items-center justify-between gap-3">
        <Checkbox
          checked={anonymous}
          onChange={(e) => setAnonymous(e.target.checked)}
          className="w-fit"
        >
          <span className="font-[family-name:var(--font-manrope)] text-[14px] text-black">
            Објави ја дискусијата анонимно
          </span>
        </Checkbox>
        {action}
      </div>
      <p className="w-1/2 font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#595959]">
        Објавувањето на оваа дискусија анонимно значи дека твојот псевдоним нема да биде видлив на
        останатите корисници.
      </p>
    </div>
  );
}
