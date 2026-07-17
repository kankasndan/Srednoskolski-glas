"use client";

import { useState } from "react";
import FieldLabel from "@/components/FieldLabel";

const MAX_LENGTH = 100;

export default function TitleInput() {
  const [value, setValue] = useState("");

  return (
    <div className="flex flex-col gap-2">
      <FieldLabel htmlFor="title" required>
        Наслов
      </FieldLabel>
      <div className="flex w-[632px] max-w-full flex-col gap-1">
        <input
          id="title"
          name="title"
          type="text"
          required
          maxLength={MAX_LENGTH}
          value={value}
          onChange={(event) => setValue(event.target.value)}
          onInvalid={(event) =>
            event.target.setCustomValidity("Внеси барем 1 карактер.")
          }
          onInput={(event) => event.target.setCustomValidity("")}
          placeholder="Внеси наслов на дискусијата"
          className="h-10 w-full rounded-xl border border-[#CCCCCC] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 text-black placeholder:text-[#595959] focus:border-[#582FF5] focus:outline-none"
        />
        <span className="self-end font-[family-name:var(--font-manrope)] text-[12px] leading-none text-[#595959]">
          {value.length}/{MAX_LENGTH}
        </span>
      </div>
    </div>
  );
}
