import Comment from "@/components/thread/Comment";
import { CommentShiftProvider } from "@/hooks/useCommentShift";

export default function CommentList({
  comments,
  threadId,
  isAnonymousThread = false,
  isThreadOwner = false,
  onCommentCreated,
  onCommentDeleted,
  expandPath = null,
  preloadedReplies = null,
  highlightCommentId = null,
}) {
  return (
    <CommentShiftProvider>
      <div className="flex flex-col">
        {comments.map((comment) => (
          <article
            key={comment.id}
            className="group rounded-2xl border-b border-b-[#CFE9ED] bg-white px-3 py-4 hover:bg-gray-50 md:rounded-3xl md:px-4 md:py-5"
          >
            <Comment
              comment={comment}
              threadId={threadId}
              isAnonymousThread={isAnonymousThread}
              isThreadOwner={isThreadOwner}
              onCommentCreated={onCommentCreated}
              onCommentDeleted={onCommentDeleted}
              expandPath={expandPath}
              preloadedReplies={preloadedReplies}
              highlightCommentId={highlightCommentId}
              ancestorIds={[]}
            />
          </article>
        ))}
      </div>
    </CommentShiftProvider>
  );
}
