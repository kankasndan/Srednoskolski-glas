import Comment from "@/components/thread/Comment";

export default function CommentList({ comments, threadId, onCommentCreated }) {
  return (
    <div className="flex flex-col">
      {comments.map((comment) => (
        <article
          key={comment.id}
          className="rounded-3xl border-b border-b-[#CCCCCC] bg-white px-4 py-5 hover:bg-gray-50"
        >
          <Comment
            comment={comment}
            threadId={threadId}
            onCommentCreated={onCommentCreated}
          />
        </article>
      ))}
    </div>
  );
}
