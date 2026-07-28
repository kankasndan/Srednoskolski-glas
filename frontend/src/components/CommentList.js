import Comment from "@/components/Comment";

export default function CommentList({ comments }) {
  return (
    <div className="flex flex-col gap-6">
      {comments.map((comment) => (
        <article
          key={comment.id}
          className="rounded-3xl border-b border-b-[#CCCCCC] bg-white p-6 hover:bg-gray-50"
        >
          <Comment comment={comment} />
        </article>
      ))}
    </div>
  );
}
