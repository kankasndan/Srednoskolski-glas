import ForumItem from "@/components/ForumItem";

export default function ThematicForums({ forums, loading, error, selectedKey, onSelect, collapsed }) {
  return (
    <div className={`mt-8 flex flex-col transition-[width] duration-300 ease-in-out ${collapsed ? "w-10" : "w-[268px]"}`}>
      <h2
        className={`mb-3 h-[22px] font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-[22px] text-[#0A0A0A] ${
          collapsed ? "invisible w-10" : "w-[268px]"
        }`}
      >
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
        <div className="flex flex-col gap-2">
          {forums.map((forum) => (
            <ForumItem
              key={forum.slug}
              forum={forum}
              active={selectedKey === `forum:${forum.slug}`}
              onSelect={onSelect}
              collapsed={collapsed}
            />
          ))}
        </div>
      )}
    </div>
  );
}
