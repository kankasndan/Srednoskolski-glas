import ForumIcon from "@/components/forum/ForumIcon";

export default function ForumOption({ forum, selected = false, onSelect }) {
  return (
    <button
      type="button"
      role="option"
      aria-selected={selected}
      onClick={() => onSelect(forum)}
      className={`flex h-10 w-full cursor-pointer items-center gap-3 px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#CFE9ED] ${
        selected ? "bg-[#CFE9ED]" : ""
      }`}
    >
      <ForumIcon src={forum.imageUrl} />
      <span>{forum.name}</span>
    </button>
  );
}
