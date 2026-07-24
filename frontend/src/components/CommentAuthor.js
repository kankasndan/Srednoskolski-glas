import { formatPostedAgo } from "@/lib/time";

export default function CommentAuthor({ author, createdAt }) {
  return (
    <div className="flex min-h-8 items-center gap-3">
      <span className="text-[14px] font-bold leading-none text-black">
        {author.username}
      </span>
      {author.school?.name ? (
        <span className="text-[12px] leading-none text-black">
          {author.school.name}
        </span>
      ) : null}
      <span className="text-[12px] leading-none text-black">
        {formatPostedAgo(createdAt)}
      </span>
    </div>
  );
}
