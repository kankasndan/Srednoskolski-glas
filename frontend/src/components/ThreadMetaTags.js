import Image from "next/image";

function MetaTag({ tag }) {
  return (
    <span className="flex h-6 shrink-0 items-center gap-1 rounded-md bg-[#F5F5F5] px-2 text-[12px] font-bold leading-none text-black">
      {tag.icon ? (
        <span className="relative size-4 shrink-0 overflow-hidden">
          <Image
            src={tag.icon}
            alt=""
            width={tag.zoom ? 40 : 16}
            height={tag.zoom ? 40 : 16}
            className={`absolute left-1/2 top-1/2 max-w-none -translate-x-1/2 -translate-y-1/2 ${
              tag.zoom ? "size-10" : "size-4"
            }`}
          />
        </span>
      ) : null}
      {tag.label}
    </span>
  );
}

export default function ThreadMetaTags({ tags, postedAgo }) {
  return (
    <div className="flex items-center gap-2">
      {tags.map((tag) => (
        <MetaTag key={tag.label} tag={tag} />
      ))}
      <span className="text-[12px] leading-none text-[#595959]">
        {postedAgo}
      </span>
    </div>
  );
}
