import { getCommentReplies } from "@/api/comments";

function commentId(value) {
  return Number(value);
}

// Ja bara patekata do komentar shto seushte ne e vchitan (spodelen link kon
// odgovor). Vrakja { path, replies }: roditelite od najgorniot nadolu i
// odgovorite vchitani po pat, za da ne se baraat po vtor pat.
export async function findCommentPath(comments, targetId) {
  const target = commentId(targetId);

  if (!Number.isFinite(target)) {
    return null;
  }

  for (const comment of comments ?? []) {
    if (commentId(comment.id) === target) {
      return { path: [], replies: {} };
    }

    const found = await searchBranch(comment, target);

    if (found) {
      return found;
    }
  }

  return null;
}

async function searchBranch(comment, target) {
  if ((comment.replies_count ?? 0) === 0) {
    return null;
  }

  const replies = await getCommentReplies(comment.id).catch(() => []);

  if (replies.some((reply) => commentId(reply.id) === target)) {
    return { path: [commentId(comment.id)], replies: { [comment.id]: replies } };
  }

  for (const reply of replies) {
    const deeper = await searchBranch(reply, target);

    if (deeper) {
      return {
        path: [commentId(comment.id), ...deeper.path],
        replies: { ...deeper.replies, [comment.id]: replies },
      };
    }
  }

  return null;
}
