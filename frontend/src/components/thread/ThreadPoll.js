"use client";

import { useState } from "react";
import Cookies from "js-cookie";
import { API_BASE_URL, ensureCsrfCookie } from "@/lib/api";

function formatEndsAt(endsAt) {
  if (!endsAt) return null;
  try {
    return new Date(endsAt).toLocaleString("mk-MK", {
      day: "numeric",
      month: "short",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch {
    return null;
  }
}

async function voteOnPoll(pollId, pollOptionId) {
  await ensureCsrfCookie();

  const response = await fetch(`${API_BASE_URL}/api/polls/${pollId}/vote`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-XSRF-TOKEN": Cookies.get("XSRF-TOKEN") ?? "",
    },
    body: JSON.stringify({ poll_option_id: pollOptionId }),
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || "Неуспешно гласање.");
    error.status = response.status;
    throw error;
  }

  return body.data;
}

export default function ThreadPoll({ poll: initialPoll }) {
  const [poll, setPoll] = useState(initialPoll);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  if (!poll) return null;

  const hasVoted = poll.user_voted_option_id != null;
  const showResults = hasVoted || poll.has_ended;
  const endsLabel = formatEndsAt(poll.ends_at);

  async function handleVote(optionId) {
    if (showResults || submitting) return;

    setSubmitting(true);
    setError("");

    try {
      const updated = await voteOnPoll(poll.id, optionId);
      setPoll(updated);
    } catch (err) {
      if (err.status === 401) {
        setError("Мора да си најавен за да гласаш.");
      } else {
        setError(err.message || "Неуспешно гласање.");
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="flex flex-col gap-3 rounded-2xl border border-[#CCCCCC] bg-white p-4">
      <div className="flex items-start justify-between gap-3">
        <div className="flex flex-col gap-1">
          <span className="font-[family-name:var(--font-manrope)] text-[12px] font-bold uppercase tracking-wide text-[#595959]">
            Анкета
          </span>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-bold text-black">
            {poll.question}
          </p>
        </div>
        {endsLabel ? (
          <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
            {poll.has_ended ? "Завршена" : `До ${endsLabel}`}
          </span>
        ) : null}
      </div>

      <div className="flex flex-col gap-2">
        {(poll.options ?? []).map((option) => {
          const selected = poll.user_voted_option_id === option.id;
          const percentage = option.percentage ?? 0;

          if (showResults) {
            return (
              <div
                key={option.id}
                className={`relative overflow-hidden rounded-xl border px-4 py-3 ${
                  selected ? "border-[#582FF5]" : "border-[#CCCCCC]"
                }`}
              >
                <div
                  className={`absolute inset-y-0 left-0 ${
                    selected ? "bg-[#EDE7FE]" : "bg-[#F5F5F5]"
                  }`}
                  style={{ width: `${percentage}%` }}
                />
                <div className="relative z-10 flex items-center justify-between gap-3">
                  <span className="font-[family-name:var(--font-manrope)] text-[14px] text-black">
                    {option.label}
                  </span>
                  <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[13px] font-bold text-[#595959]">
                    {percentage}%
                  </span>
                </div>
              </div>
            );
          }

          return (
            <button
              key={option.id}
              type="button"
              disabled={submitting}
              onClick={() => handleVote(option.id)}
              className="rounded-xl border border-[#CCCCCC] px-4 py-3 text-left font-[family-name:var(--font-manrope)] text-[14px] text-black transition-colors hover:border-[#582FF5] hover:bg-[#F8F6FF] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {option.label}
            </button>
          );
        })}
      </div>

      <p className="font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
        {poll.total_votes ?? 0}{" "}
        {(poll.total_votes ?? 0) === 1 ? "глас" : "гласови"}
      </p>

      {error ? (
        <p className="font-[family-name:var(--font-manrope)] text-[12px] text-[var(--color-error)]">
          {error}
        </p>
      ) : null}
    </div>
  );
}
