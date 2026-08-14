"use client";

import { useEffect, useState } from "react";
import {
  findUserThread,
  getUserPollVote,
  normalizePoll,
  voteOnPoll,
} from "@/lib/userThreads";

function formatVoteCount(count) {
  if (count === 1) return "1 глас";
  return `${count} гласови`;
}

export default function ThreadPoll({ threadId, poll: initialPoll }) {
  const [poll, setPoll] = useState(initialPoll);
  const [selectedOption, setSelectedOption] = useState(null);

  useEffect(() => {
    const storedThread = findUserThread(threadId);
    const storedPoll = normalizePoll(storedThread) || initialPoll;
    setPoll(storedPoll);
    setSelectedOption(getUserPollVote(threadId));
  }, [threadId, initialPoll]);

  if (!poll?.options?.length) return null;

  const totalVotes = poll.options.reduce((sum, option) => sum + option.votes, 0);

  function handleVote(optionId) {
    const updatedThread = voteOnPoll(threadId, optionId);
    const updatedPoll = normalizePoll(updatedThread);

    if (updatedPoll) {
      setPoll(updatedPoll);
      setSelectedOption(getUserPollVote(threadId));
    }
  }

  return (
    <section className="mt-6 rounded-xl border border-[#E6E6E6] bg-[#FAFAFA] p-4">
      <div className="mb-4 flex items-start justify-between gap-4">
        <h3 className="font-[family-name:var(--font-manrope)] text-[16px] font-bold text-[#0A0A0A]">
          {poll.question}
        </h3>
        <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
          {formatVoteCount(totalVotes)}
        </span>
      </div>

      <div className="flex flex-col gap-2">
        {poll.options.map((option) => {
          const percentage = totalVotes ? Math.round((option.votes / totalVotes) * 100) : 0;
          const isSelected = selectedOption === option.id;

          return (
            <button
              key={option.id}
              type="button"
              onClick={() => handleVote(option.id)}
              className={`relative overflow-hidden rounded-xl border px-4 py-3 text-left transition-colors ${
                isSelected
                  ? "border-[#582FF5] bg-[#F5F2FF]"
                  : "border-[#E6E6E6] bg-white hover:border-[#582FF5]/50"
              }`}
            >
              <div
                className="absolute inset-y-0 left-0 bg-[#582FF5]/15 transition-all duration-300"
                style={{ width: `${percentage}%` }}
              />
              <div className="relative flex items-center justify-between gap-3">
                <div className="flex min-w-0 items-center gap-3">
                  <span
                    className={`flex size-4 shrink-0 items-center justify-center rounded-full border ${
                      isSelected ? "border-[#582FF5] bg-[#582FF5]" : "border-[#CCCCCC] bg-white"
                    }`}
                  >
                    {isSelected && <span className="size-1.5 rounded-full bg-white" />}
                  </span>
                  <span className="truncate font-[family-name:var(--font-manrope)] text-[14px] text-[#0A0A0A]">
                    {option.text}
                  </span>
                </div>
                <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[13px] font-medium text-[#595959]">
                  {option.votes} · {percentage}%
                </span>
              </div>
            </button>
          );
        })}
      </div>
    </section>
  );
}
