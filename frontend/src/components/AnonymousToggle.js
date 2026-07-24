"use client";

import { useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;

export default function AnonymousToggle({ className = "", action }) {
  const [anonymous, setAnonymous] = useState(false);

  return (
    <div className={`flex flex-col gap-1 ${className}`}>
      <div className="flex items-center justify-between gap-3">
        <button
          type="button"
          aria-pressed={anonymous}
          onClick={() => setAnonymous((prev) => !prev)}
          className="flex w-fit cursor-pointer items-center gap-2"
        >
          <span
            className={`flex size-4 shrink-0 items-center justify-center rounded border border-[#582FF5] ${
              anonymous ? "bg-[#582FF5]" : "bg-white"
            }`}
          >
            {anonymous && <FontAwesomeIcon icon={faCheck} className="h-2.5 text-white" />}
          </span>
          <span className="font-[family-name:var(--font-manrope)] text-[14px] text-black">
            Објави ја дискусијата анонимно
          </span>
        </button>
        {action}
      </div>
      <p className="w-1/2 font-[family-name:var(--font-manrope)] text-[12px] leading-5 text-[#595959]">
        Објавувањето на оваа дискусија анонимно значи дека твојот псевдоним нема да биде видлив на
        останатите корисници.
      </p>
    </div>
  );
}
