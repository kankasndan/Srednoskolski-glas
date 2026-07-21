"use client";

import Image from "next/image";
import { useState } from "react";

const SORT_OPTIONS = ["Најдобри", "Најнови", "Најстари"];

export default function CommentsHeader({ count }) {
  const [open, setOpen] = useState(false);
  const [selected, setSelected] = useState("Најдобри");

  return (
    <div className="flex items-center justify-between gap-4">
      <h2 className="flex h-10 items-center text-[16px] font-bold leading-[19.5px] text-black">
        {count} коментари
      </h2>

      <div className="relative w-36">
        <button
          type="button"
          onClick={() => setOpen(!open)}
          className={`flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#CCCCCC] bg-[#CFE9ED] p-2 text-[14px] font-bold leading-none text-black ${
            open ? "rounded-b-none" : ""
          }`}
        >
          {selected}
          <Image
            src="/chevron-down.svg"
            alt=""
            width={16}
            height={16}
            className={`size-4 ${open ? "rotate-180" : ""}`}
          />
        </button>

        {open ? (
          <ul className="absolute top-full right-0 left-0 overflow-hidden rounded-b-xl border border-t-0 border-[#CCCCCC] bg-white">
            {SORT_OPTIONS.map((option) => (
              <li key={option}>
                {/* TODO sortiranje koga kje ima endpoint */}
                <button
                  type="button"
                  onClick={() => {
                    setSelected(option);
                    setOpen(false);
                  }}
                  className={`w-full cursor-pointer border-b border-[#CCCCCC] px-4 py-2 text-[14px] leading-none transition-colors last:border-b-0 ${
                    selected === option
                      ? "bg-[#CFE9ED] font-bold text-black"
                      : "text-black hover:bg-[#E5E5E5]"
                  }`}
                >
                  {option}
                </button>
              </li>
            ))}
          </ul>
        ) : null}
      </div>
    </div>
  );
}
