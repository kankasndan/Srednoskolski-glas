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
    <PillButton
      label="Анонимно - никој не го гледа името"
      selected={anonymous}
      onClick={() => setAnonymous((prev) => !prev)}
      className={className}
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
  );
}
