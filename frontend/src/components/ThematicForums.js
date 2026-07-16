"use client";

import ForumItem from "@/components/ForumItem";
import { useForums } from "@/hooks/useForums";

export default function ThematicForums({ selectedKey, onSelect }) {
  const { forums, loading, error } = useForums();

  return (
    <div className="mt-8 flex w-[268px] flex-col">
      <h2 className="mb-3 h-[22px] w-[268px] font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-[22px] text-[#0A0A0A]">
        Тематски форуми
      </h2>
      {loading ? (
        <p className="w-[268px] font-[family-name:var(--font-manrope)] text-[13px] font-normal text-[#595959]">
          Се вчитува…
        </p>
      ) : error ? (
        <p className="w-[268px] font-[family-name:var(--font-manrope)] text-[13px] font-normal text-[#595959]">
          Не успеа вчитувањето на форумите.
        </p>
      ) : (
        forums.map((forum) => (
          <ForumItem
            key={forum.slug}
            forum={forum}
            selectedKey={selectedKey}
            onSelect={onSelect}
          />
        ))
      )}
    </div>
  );
}
