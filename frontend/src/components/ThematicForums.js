import ForumItem from "@/components/ForumItem";
import { FORUMS } from "@/lib/forums";

export default function ThematicForums({ selectedKey, onSelect }) {
  return (
    <div className="mt-8 flex w-[268px] flex-col">
      <h2 className="mb-3 h-[22px] w-[268px] font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-[22px] text-[#0A0A0A]">
        Тематски форуми
      </h2>
      <div className="flex flex-col gap-2">
        {FORUMS.map((forum) => (
          <ForumItem
            key={forum.slug}
            forum={forum}
            selectedKey={selectedKey}
            onSelect={onSelect}
          />
        ))}
      </div>
    </div>
  );
}
