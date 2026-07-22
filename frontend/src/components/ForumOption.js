import ForumIcon from "@/components/ForumIcon";

export default function ForumOption({ forum, onSelect }) {
  return (
    <button
      type="button"
      onClick={() => onSelect(forum)}
      className="flex h-10 w-full items-center gap-3 px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#CFE9ED]"
    >
      <ForumIcon src={forum.imageUrl} />
      <span>{forum.name}</span>
    </button>
  );
}
