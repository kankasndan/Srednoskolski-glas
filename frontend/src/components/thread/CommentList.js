import Comment from "@/components/thread/Comment";

export default function CommentList({ comments, threadId, onCommentCreated }) {
  return (
    <div className="flex flex-col">
      {comments.map((comment) => (
        <article
          key={comment.id}
          className="rounded-2xl border-b border-b-[#CFE9ED] bg-white px-3 py-4 hover:bg-gray-50 md:rounded-3xl md:px-4 md:py-5"
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
