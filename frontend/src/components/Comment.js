"use client";

import Image from "next/image";
import { useState } from "react";
import CommentActions from "@/components/CommentActions";
import CommentAuthor from "@/components/CommentAuthor";
import CommentBody from "@/components/CommentBody";
import CommentComposer from "@/components/CommentComposer";

export default function Comment({ comment, depth = 0 }) {
  const [collapsed, setCollapsed] = useState(false);
  const [replying, setReplying] = useState(false);
  const replies = comment.replies ?? [];
  const hasReplies = replies.length > 0;
  const showThread = !collapsed && hasReplies;
  const showLine = hasReplies || depth > 0;

  return (
    <div className="flex gap-2">
      <div className="flex shrink-0 flex-col items-center">
        <Image
          src={comment.author.imageUrl}
          alt=""
          width={32}
          height={32}
          className="size-8 rounded-full object-cover"
        />
        {showLine ? (
          <div className="mt-1 w-0.5 flex-1 rounded-xs bg-[#CFE9ED]" />
        ) : null}
      </div>

      <div className="flex flex-1 flex-col gap-3">
        <CommentAuthor author={comment.author} createdAt={comment.created_at} />
        <CommentBody text={comment.content} muted={depth === 0} />
        <CommentActions
          votes={comment.upvotes}
          hasReplies={hasReplies}
          collapsed={collapsed}
          onToggle={() => setCollapsed(!collapsed)}
          onReply={() => setReplying(!replying)}
        />

        {replying ? (
          <CommentComposer compact onClose={() => setReplying(false)} />
        ) : null}

        {showThread ? (
          <div className="flex flex-col gap-4 pt-1">
            {replies.map((reply) => (
              <Comment key={reply.id} comment={reply} depth={depth + 1} />
            ))}
          </div>
        ) : null}
      </div>
    </div>
  );
}
