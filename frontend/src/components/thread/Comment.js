"use client";

import { useState } from "react";
import Avatar from "@/components/ui/Avatar";
import CommentActions from "@/components/thread/CommentActions";
import CommentAuthor from "@/components/thread/CommentAuthor";
import CommentBody from "@/components/thread/CommentBody";
import CommentComposer from "@/components/thread/CommentComposer";
import { reportComment, reportErrorMessage } from "@/api/moderation";
import InfoDialog from "@/components/ui/InfoDialog";
import ReportDialog from "@/components/ui/ReportDialog";
import { formatPostedAgo } from "@/lib/time";

export default function Comment({ comment, threadId, onCommentCreated, depth = 0 }) {
  const [collapsed, setCollapsed] = useState(false);
  const [replying, setReplying] = useState(false);
  const [reporting, setReporting] = useState(false);
  const [reported, setReported] = useState(false);
  const replies = comment.replies ?? [];
  const hasReplies = replies.length > 0;
  const showThread = !collapsed && hasReplies;
  const showLine = hasReplies || depth > 0;

  async function handleReport({ reason, details }) {
    try {
      await reportComment(comment.id, { reason, details });
      setReporting(false);
      setReported(true);
    } catch (error) {
      const next = new Error(reportErrorMessage(error));
      next.status = error?.status;
      next.body = error?.body;
      throw next;
    }
  }

  return (
    <div id={`comment-${comment.id}`} className="flex min-w-0 gap-2 scroll-mt-24">
      <div className="flex shrink-0 flex-col items-center">
        <Avatar src={comment.author.imageUrl} size="md" />
        {showLine ? (
          <div className="mt-1 w-0.5 flex-1 rounded-xs bg-[#CFE9ED]" />
        ) : null}
      </div>

      <div className="flex min-w-0 flex-1 flex-col gap-3">
        <CommentAuthor author={comment.author} />
        <CommentBody text={comment.content} muted={depth === 0} />
        <CommentActions
          commentId={comment.id}
          votes={comment.upvotes}
          hasVoted={comment.has_voted}
          hasReplies={hasReplies}
          collapsed={collapsed}
          onToggle={() => setCollapsed(!collapsed)}
          onReply={() => setReplying(!replying)}
          onReport={() => setReporting(true)}
          createdAtLabel={formatPostedAgo(comment.created_at)}
        />

        {reporting && (
          <ReportDialog
            open
            onClose={() => setReporting(false)}
            onSubmit={handleReport}
          />
        )}

        <InfoDialog
          open={reported}
          title="Пријавата беше успешно поднесена и испратена до админот."
          onClose={() => setReported(false)}
        />

        {replying ? (
          <CommentComposer
            compact
            threadId={threadId}
            parentId={comment.id}
            onClose={() => setReplying(false)}
            onCreated={() => onCommentCreated?.()}
          />
        ) : null}

        {showThread ? (
          <div className="flex min-w-0 flex-col gap-4 pt-1">
            {replies.map((reply) => (
              <Comment
                key={reply.id}
                comment={reply}
                threadId={threadId}
                onCommentCreated={onCommentCreated}
                depth={depth + 1}
              />
            ))}
          </div>
        ) : null}
      </div>
    </div>
  );
}
