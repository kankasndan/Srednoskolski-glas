"use client";

import { useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck } from "@fortawesome/free-solid-svg-icons";
import PillButton from "@/components/PillButton";

config.autoAddCss = false;

export default function AnonymousToggle({ className = "" }) {
  const [anonymous, setAnonymous] = useState(false);

  return (
    <div className={`group relative ${className}`}>
      <PillButton
        label="Објави ја дискусијата анонимно"
        selected={anonymous}
        onClick={() => setAnonymous((prev) => !prev)}
        className="w-full"
        leading={
          <span
            className={`flex size-4 shrink-0 items-center justify-center rounded border ${
              anonymous ? "border-[#582FF5] bg-[#582FF5]" : "border-[#CCCCCC] "
            }`}
          >
            {anonymous && <FontAwesomeIcon icon={faCheck} className="h-2.5 text-white" />}
          </span>
        }
      />
      <div
        role="tooltip"
        className="pointer-events-none absolute left-0 top-full z-20 mt-2 w-full rounded-xl border border-[#CCCCCC] bg-white p-3 text-[13px] font-normal leading-5 text-[#595959] opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
      >
        Објавувањето на оваа дискусија анонимно значи дека твојот псевдоним нема да биде видлив на останатите корисници.
      </div>
    </div>
  );
}
