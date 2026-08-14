"use client";

import { useState } from "react";
import Avatar from "@/components/ui/Avatar";
import CommentActions from "@/components/thread/CommentActions";
import CommentAuthor from "@/components/thread/CommentAuthor";
import CommentBody from "@/components/thread/CommentBody";
import CommentComposer from "@/components/thread/CommentComposer";
import { getCommentReplies } from "@/api/comments";
import { reportComment, reportErrorMessage } from "@/api/moderation";
import InfoDialog from "@/components/ui/InfoDialog";
import ReportDialog from "@/components/ui/ReportDialog";

export default function Comment({ comment, threadId, onCommentCreated, depth = 0 }) {
  const [expanded, setExpanded] = useState(false);
  const [replies, setReplies] = useState([]);
  const [repliesCount, setRepliesCount] = useState(comment.replies_count ?? 0);
  const [loadingReplies, setLoadingReplies] = useState(false);
  const [replying, setReplying] = useState(false);
  const [reporting, setReporting] = useState(false);
  const [reported, setReported] = useState(false);
  const hasReplies = repliesCount > 0;
  const showThread = expanded && (replies.length > 0 || loadingReplies);
  const showLine = hasReplies || depth > 0 || showThread;

  async function loadReplies() {
    setLoadingReplies(true);
    try {
      const next = await getCommentReplies(comment.id);
      setReplies(next);
      setRepliesCount(next.length);
    } catch {
      setReplies([]);
    } finally {
      setLoadingReplies(false);
    }
  }

  async function toggleReplies() {
    if (loadingReplies) return;

    if (expanded) {
      setExpanded(false);
      return;
    }

    setExpanded(true);
    if (replies.length === 0 && repliesCount > 0) {
      await loadReplies();
    }
  }

  async function handleReplyCreated(created) {
    onCommentCreated?.(created);
    setReplying(false);
    setRepliesCount((count) => count + 1);
    setExpanded(true);
    await loadReplies();
  }

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
    <div className="flex gap-2">
      <div className="flex shrink-0 flex-col items-center">
        <Avatar src={comment.author.imageUrl} size="md" />
        {showLine ? (
          <div className="mt-1 w-0.5 flex-1 rounded-xs bg-[#CFE9ED]" />
        ) : null}
      </div>

      <div className="flex flex-1 flex-col gap-3">
        <CommentAuthor author={comment.author} createdAt={comment.created_at} />
        <CommentBody text={comment.content} muted={depth === 0} />
        <CommentActions
          commentId={comment.id}
          votes={comment.upvotes}
          hasVoted={comment.has_voted}
          repliesCount={repliesCount}
          expanded={expanded}
          loadingReplies={loadingReplies}
          onToggle={toggleReplies}
          onReply={() => setReplying(!replying)}
          onReport={() => setReporting(true)}
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
            onCreated={handleReplyCreated}
          />
        ) : null}

        {showThread ? (
          <div className="flex flex-col gap-4 pt-1">
            {loadingReplies && replies.length === 0 ? (
              <p className="text-[13px] text-[#999999]">Се вчитуваат одговорите…</p>
            ) : (
              replies.map((reply) => (
                <Comment
                  key={reply.id}
                  comment={reply}
                  threadId={threadId}
                  onCommentCreated={onCommentCreated}
                  depth={depth + 1}
                />
              ))
            )}
          </div>
        ) : null}
      </div>
    </div>
  );
}
