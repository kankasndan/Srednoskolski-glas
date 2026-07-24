"use client";

import { useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faPlus, faXmark } from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;

const MIN_OPTIONS = 2;
const MAX_OPTIONS = 4;

const INPUT_CLASS =
  "h-10 w-full rounded-xl border border-[#CCCCCC] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 text-black placeholder:text-[#595959] focus:border-[#582FF5] focus:outline-none";

export default function PollAttachment({ onClose }) {
  const [title, setTitle] = useState("");
  const [options, setOptions] = useState(["", ""]);

  function updateOption(index, value) {
    setOptions((prev) => prev.map((option, i) => (i === index ? value : option)));
  }

  function addOption() {
    setOptions((prev) => (prev.length < MAX_OPTIONS ? [...prev, ""] : prev));
  }

  function removeOption(index) {
    setOptions((prev) => (prev.length > MIN_OPTIONS ? prev.filter((_, i) => i !== index) : prev));
  }

  return (
    <div className="flex flex-col gap-3 rounded-2xl border border-[#CCCCCC] bg-white p-4">
      <div className="flex items-center justify-between">
        <span className="font-[family-name:var(--font-manrope)] text-[14px] font-bold text-black">
          Анкета
        </span>
        <button
          type="button"
          aria-label="Отстрани ја анкетата"
          onClick={onClose}
          className="flex size-6 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>
      </div>

      <input
        type="text"
        value={title}
        onChange={(event) => setTitle(event.target.value)}
        placeholder="Прашање за анкетата"
        className={INPUT_CLASS}
      />

      <div className="flex flex-col gap-2">
        {options.map((option, index) => (
          <div key={index} className="flex items-center gap-2">
            <input
              type="text"
              value={option}
              onChange={(event) => updateOption(index, event.target.value)}
              placeholder={`Опција ${index + 1}`}
              className={INPUT_CLASS}
            />
            {options.length > MIN_OPTIONS && (
              <button
                type="button"
                aria-label={`Отстрани ја опцијата ${index + 1}`}
                onClick={() => removeOption(index)}
                className="flex size-6 shrink-0 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
              >
                <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
              </button>
            )}
          </div>
        ))}
      </div>

      {options.length < MAX_OPTIONS && (
        <button
          type="button"
          onClick={addOption}
          className="flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-[#CCCCCC] font-[family-name:var(--font-manrope)] text-[14px] text-[#595959] transition-colors hover:bg-[#DCEBED] hover:text-black"
        >
          <FontAwesomeIcon icon={faPlus} className="h-4 w-4" />
          Додади опција
        </button>
      )}
    </div>
  );
}
