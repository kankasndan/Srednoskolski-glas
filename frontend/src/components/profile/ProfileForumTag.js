export default function ProfileForumTag({ forum }) {
  return (
    <span className="flex h-6 items-center gap-2 rounded-md border-[0.5px] border-(--color-grays-300) bg-gray-100 px-2 py-1 font-(family-name:--font-roboto) text-[12px] leading-4">
      <span className="flex size-4 shrink-0 items-center justify-center">
        <img
          src={forum.imageUrl || "/avatars/default-1.svg"}
          alt=""
          className="size-full object-contain"
        />
      </span>
      {forum.name}
    </span>
  );
}
