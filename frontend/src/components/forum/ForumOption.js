import ForumIcon from "@/components/forum/ForumIcon";

export default function ForumOption({ forum, onSelect }) {
  const isSchool = forum.type === "school";

  return (
    <button
      type="button"
      onClick={() => onSelect(forum)}
      className="flex h-10 w-full items-center gap-3 px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#CFE9ED]"
    >
      <ForumIcon
        src={forum.imageUrl}
        imageClassName={isSchool ? "size-4" : "size-9 max-w-none"}
      />
      <span>{forum.name}</span>
    </button>
  );
}
