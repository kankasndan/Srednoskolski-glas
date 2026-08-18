import { getCommentReplies } from "@/api/comments";

// Ja bara patekata do komentar shto seushte ne e vchitan (spodelen link kon
// odgovor). Vrakja { path, replies }: roditelite od najgorniot nadolu i
// odgovorite vchitani po pat, za da ne se baraat po vtor pat.
export async function findCommentPath(comments, targetId) {
  const target = Number(targetId);

  if (!Number.isFinite(target)) {
    return null;
  }

  for (const comment of comments ?? []) {
    if (comment.id === target) {
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
  // Odgovorot ima pogolemo id od roditelot, pa granka so pogolem koren otpagja.
  if (comment.id > target || (comment.replies_count ?? 0) === 0) {
    return null;
  }

  const replies = await getCommentReplies(comment.id).catch(() => []);

  // Prvo celoto nivo, pa duri potoa podlaboko — pobrzo koga celta e plitka.
  if (replies.some((reply) => reply.id === target)) {
    return { path: [comment.id], replies: { [comment.id]: replies } };
  }

  for (const reply of replies) {
    const deeper = await searchBranch(reply, target);

    if (deeper) {
      return {
        path: [comment.id, ...deeper.path],
        replies: { ...deeper.replies, [comment.id]: replies },
      };
    }
  }

  return null;
}
