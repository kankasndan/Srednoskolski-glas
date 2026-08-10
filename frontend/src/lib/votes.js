/** Optimistic upvote toggle: update UI first, roll back on API failure. */
export function nextVoteState(votes = 0, hasVoted = false) {
  const previousVotes = Number(votes) || 0;
  const previousHasVoted = Boolean(hasVoted);
  const nextHasVoted = !previousHasVoted;

  return {
    previousVotes,
    previousHasVoted,
    nextHasVoted,
    nextVotes: Math.max(0, previousVotes + (nextHasVoted ? 1 : -1)),
  };
}
