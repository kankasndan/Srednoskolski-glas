"use client";

import { useState } from "react";
import FieldLabel from "@/components/FieldLabel";
import PillButton from "@/components/PillButton";

const TYPES = ["Слика", "Видео", "Датотека", "Линк", "Анкета"];

export default function PostTypeButtons() {
  const [selected, setSelected] = useState(null);

  return (
    <div className="flex flex-col gap-2">
      <FieldLabel>Додади прилог (опционално)</FieldLabel>
      <div className="flex w-[632px] max-w-full gap-3">
        {TYPES.map((type) => (
          <PillButton
            key={type}
            label={type}
            selected={selected === type}
            onClick={() => setSelected((prev) => (prev === type ? null : type))}
            className="flex-1"
          />
        ))}
      </div>
    </div>
  );
}
