"use client";

import { use, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import AppShell from "@/components/AppShell";
import { FEED_THREAD_LIST } from "@/lib/threads";

export default function ThreadDetailPage({ params }) {
  const { id } = use(params);
  const threadId = parseInt(id, 10);
  const thread = FEED_THREAD_LIST.find((t) => t.id === threadId);

  const [isFollowing, setIsFollowing] = useState(false);

  if (!thread) {
    return (
      <AppShell>
        <div className="flex w-[990px] max-w-full flex-col items-center gap-6 py-16">
          <h1 className="font-[family-name:var(--font-manrope)] text-[28px] font-bold text-black">
            Дискусијата не е пронајдена
          </h1>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] text-[#595959]">
            Оваа дискусија не постои или е отстранета.
          </p>
          <Link
            href="/feed"
            className="flex h-10 items-center gap-2 rounded-[12px] bg-[#582FF5] px-6 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4a22d4]"
          >
            ← Назад кон дискусии
          </Link>
        </div>
      </AppShell>
    );
  }

  // Get initials for avatars
  const getInitials = (name) => {
    if (!name) return "";
    return name.slice(0, 2).toUpperCase();
  };

  // Mock comments matching the screenshot exactly
  const COMMENTS = [
    {
      id: 1,
      author: "ана_2027",
      school: "СУГС Михајло Пупин",
      postedAgo: "пред 1ч.",
      text: "Кај мене е математика изненадувачки — го сакам начинот на кој нашиот професор ги објаснува работите. Порано ми беше здодевна, сега не можам да чекам час.",
      votes: 24,
      replies: [
        {
          id: 2,
          author: "марко_2026",
          school: "Гим. Орце Николов",
          postedAgo: "пред 45мин.",
          text: "Тоа е разликата помеѓу добар и лош професор! Убаво е кога некој може да те запали за предметот.",
          votes: 11,
          replies: [
            {
              id: 3,
              author: "ана_2027",
              school: "СУГС Михајло Пупин",
              postedAgo: "пред 30мин.",
              text: "Точно! @марко_2026 мислам дека многу зависи и од нашиот пристап исто така.",
              votes: 4,
              replies: []
            }
          ]
        }
      ]
    }
  ];

  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-8 pb-16">
        {/* Back navigation */}
        <Link
          href="/feed"
          className="group flex w-fit items-center gap-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium text-[#595959] transition-colors hover:text-[#582FF5]"
        >
          <span className="text-[18px] font-normal leading-none">‹</span>
          Назад кон Општи дискусии
        </Link>

        {/* Thread header */}
        <article className="flex flex-col gap-6 rounded-[24px] border border-[#E6E6E6] bg-white p-8 shadow-[0_4px_24px_rgba(0,0,0,0.04)]">
          {/* Tags row with Share and Menu icons */}
          <div className="flex items-center justify-between">
            <div className="flex max-w-[80%] flex-wrap items-center gap-2">
              {thread.tags.map((tag) => (
                <span
                  key={tag.label}
                  className={`flex h-7 shrink-0 items-center rounded-[8px] px-3 font-[family-name:var(--font-roboto)] text-[13px] font-normal leading-4 text-black ${
                    tag.tone === "highlight"
                      ? "bg-[#F0E92F] border-[0.5px] border-[#CCCCCC]"
                      : "bg-[#E6E6E6] border-[0.5px] border-[#CCCCCC]"
                  }`}
                  style={{ gap: tag.icon ? "8px" : undefined }}
                >
                  {tag.icon ? (
                    <span className="relative size-4 shrink-0 overflow-hidden">
                      <Image
                        src={tag.icon}
                        alt=""
                        width={tag.iconZoom ? 40 : 16}
                        height={tag.iconZoom ? 40 : 16}
                        className={`absolute left-1/2 top-1/2 max-w-none -translate-x-1/2 -translate-y-1/2 ${
                          tag.iconZoom ? "size-10" : "size-4"
                        }`}
                      />
                    </span>
                  ) : null}
                  {tag.label}
                </span>
              ))}
              <span className="shrink-0 font-[family-name:var(--font-roboto)] text-[13px] font-normal leading-4 text-[#595959]">
                {thread.postedAgo}
              </span>
            </div>
            
            {/* Share and Action icons */}
            <div className="flex items-center gap-4 text-[#595959]">
              <button type="button" aria-label="Сподели" className="hover:text-[#582FF5] transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="18" cy="5" r="3" />
                  <circle cx="6" cy="12" r="3" />
                  <circle cx="18" cy="19" r="3" />
                  <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                  <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                </svg>
              </button>
              <button type="button" aria-label="Мени" className="hover:text-[#582FF5] transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="5" r="1" />
                  <circle cx="12" cy="12" r="1" />
                  <circle cx="12" cy="19" r="1" />
                </svg>
              </button>
            </div>
          </div>

          {/* Title */}
          <h1 className="font-[family-name:var(--font-manrope)] text-[28px] font-bold leading-[38px] text-black">
            {thread.title}
          </h1>

          {/* Image if present */}
          {thread.image && (
            <Image
              src={thread.image}
              alt=""
              width={990}
              height={421}
              className="h-[421px] w-full rounded-[20px] object-cover"
            />
          )}

          {/* Full content */}
          <div className="font-[family-name:var(--font-manrope)] text-[16px] leading-[26px] text-[#333333]">
            <p className="mb-4">{thread.excerpt}</p>
            <p className="mb-4">
              Ова е детален преглед на дискусијата. Тука можете да го прочитате целосниот текст, да
              оставите коментар и да гласате за одговорите кои ги сметате за корисни.
            </p>
            <p>
              Споделете го вашето искуство и помогнете им на другите ученици!
            </p>
          </div>

          {/* Action buttons footer */}
          <div className="flex items-center justify-between border-t border-[#E6E6E6] pt-6">
            <div className="flex items-center gap-2">
              <button
                type="button"
                className="flex h-10 w-24 items-center justify-center gap-4 rounded-[12px] border border-[#CCCCCC] bg-white px-4 py-2 opacity-80"
              >
                <Image src="/eye.svg" alt="" width={24} height={24} className="size-6 shrink-0" />
                <span className="min-w-0 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[19px] text-black">
                  {thread.views}
                </span>
              </button>
              <button
                type="button"
                className="flex h-10 w-24 items-center justify-center gap-4 rounded-[12px] border border-[#CCCCCC] bg-white px-4 py-2 opacity-80 transition-colors hover:border-[#582FF5] hover:bg-[#F5F2FF]"
              >
                <Image src="/chat-1-line.svg" alt="" width={24} height={24} className="size-6 shrink-0" />
                <span className="min-w-0 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[19px] text-black">
                  {thread.comments}
                </span>
              </button>
              <button
                type="button"
                className="flex h-10 w-24 items-center justify-center gap-4 rounded-[12px] border border-[#CCCCCC] bg-white px-4 py-2 opacity-80 transition-colors hover:border-[#582FF5] hover:bg-[#F5F2FF]"
              >
                <Image src="/Chevrons up.svg" alt="" width={24} height={24} className="size-6 shrink-0" />
                <span className="min-w-0 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[19px] text-black">
                  {thread.votes}
                </span>
              </button>
            </div>
            
            {/* Follow discussion button */}
            <button
              type="button"
              onClick={() => setIsFollowing(!isFollowing)}
              className={`flex h-10 items-center justify-center rounded-[12px] px-6 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-all ${
                isFollowing
                  ? "bg-[#4a22d4] shadow-inner"
                  : "bg-[#582FF5] hover:bg-[#4a22d4] hover:shadow-[0_4px_12px_rgba(88,47,245,0.25)]"
              }`}
            >
              {isFollowing ? "Следена дискусија" : "Следи ја дискусијата"}
            </button>
          </div>
        </article>

        {/* Comments section */}
        <section className="flex flex-col gap-6">
          {/* Comment input header */}
          <h3 className="font-[family-name:var(--font-manrope)] text-[16px] font-bold text-black">
            Додади коментар
          </h3>

          {/* Comment input card */}
          <div className="flex flex-col gap-4 rounded-[20px] border border-[#E6E6E6] bg-white p-6 shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <textarea
              placeholder="Сподели го своето мислење... Употреби @ за да означиш некого..."
              className="min-h-[100px] w-full resize-none rounded-[12px] border border-[#E6E6E6] bg-[#FAFAFA] px-4 py-3 font-[family-name:var(--font-manrope)] text-[14px] text-black placeholder:text-[#999] transition-colors focus:border-[#582FF5] focus:bg-white focus:outline-none"
            />
            <div className="flex items-center justify-between">
              <span className="font-[family-name:var(--font-manrope)] text-[12px] text-[#999999]">
                Внимавај на правилата на заедницата.
              </span>
              <button
                type="button"
                className="rounded-[12px] bg-[#582FF5] px-6 py-2.5 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-all hover:bg-[#4a22d4] hover:shadow-[0_4px_12px_rgba(88,47,245,0.3)]"
              >
                Објави
              </button>
            </div>
          </div>

          {/* Comments list header */}
          <div className="flex items-center justify-between border-t border-[#E6E6E6]/60 pt-6">
            <h2 className="font-[family-name:var(--font-manrope)] text-[20px] font-bold text-black">
              {thread.comments} коментари
            </h2>
            <div className="flex items-center gap-2 cursor-pointer text-black hover:text-[#582FF5]">
              <span className="font-[family-name:var(--font-manrope)] text-[14px] font-bold">Најдобри</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <polyline points="6 9 12 15 18 9" />
              </svg>
            </div>
          </div>

          {/* Recursively render comments */}
          <div className="flex flex-col gap-6">
            {COMMENTS.map((comment) => (
              <CommentNode key={comment.id} comment={comment} getInitials={getInitials} />
            ))}
          </div>
        </section>
      </div>
    </AppShell>
  );
}

function CommentNode({ comment, getInitials, depth = 0 }) {
  // Parse @mentions to highlight them purple
  const renderCommentText = (text) => {
    if (!text.includes("@")) return text;
    const parts = text.split(/(\s+)/);
    return parts.map((part, index) => {
      if (part.startsWith("@")) {
        return (
          <span key={index} className="font-bold text-[#582FF5] cursor-pointer">
            {part}
          </span>
        );
      }
      return part;
    });
  };

  return (
    <div className={`flex flex-col ${depth > 0 ? "ml-8" : ""}`}>
      <div className="flex gap-4">
        {/* User avatar badge with initials */}
        <div className="flex flex-col items-center">
          <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#582FF5] font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white shadow-sm">
            {getInitials(comment.author)}
          </div>
          {/* Vertical line indicator for sub-replies */}
          {comment.replies && comment.replies.length > 0 && (
            <div className="w-0.5 flex-1 bg-[#E6E6E6]/60 my-2" />
          )}
        </div>

        {/* Comment block */}
        <div className="flex flex-1 flex-col gap-2">
          {/* Author metadata */}
          <div className="flex flex-wrap items-center gap-2">
            <span className="font-[family-name:var(--font-manrope)] text-[14px] font-bold text-black">
              {comment.author}
            </span>
            {comment.school && (
              <span className="font-[family-name:var(--font-manrope)] text-[12px] text-[#999999]">
                {comment.school}
              </span>
            )}
            <span className="font-[family-name:var(--font-roboto)] text-[12px] text-[#999999]">
              {comment.postedAgo}
            </span>
          </div>

          {/* Comment message */}
          <p className="font-[family-name:var(--font-manrope)] text-[14px] leading-[22px] text-[#333333]">
            {renderCommentText(comment.text)}
          </p>

          {/* Action links */}
          <div className="flex flex-wrap items-center gap-4 pt-1 text-[13px] text-[#595959]">
            {/* Upvote capsule */}
            <button
              type="button"
              className="flex items-center gap-1.5 rounded-full border border-[#E6E6E6] bg-white px-3 py-1 font-[family-name:var(--font-manrope)] text-[#595959] transition-all hover:border-[#582FF5] hover:text-[#582FF5] hover:shadow-sm cursor-pointer"
            >
              <svg width="12" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" className="text-[#582FF5]">
                <path d="M17 11L12 6L7 11M17 18L12 13L7 18" />
              </svg>
              <span className="font-bold text-black">{comment.votes}</span>
            </button>

            {/* Reply */}
            <button
              type="button"
              className="flex items-center gap-1.5 font-[family-name:var(--font-manrope)] hover:text-[#582FF5] transition-colors"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
              </svg>
              <span>Одговори</span>
            </button>

            {/* Report */}
            <button
              type="button"
              className="flex items-center gap-1.5 font-[family-name:var(--font-manrope)] hover:text-[#582FF5] transition-colors"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                <line x1="4" y1="22" x2="4" y2="15" />
              </svg>
              <span>Пријави</span>
            </button>

            {/* Show less (only for parent comments to collapse) */}
            {depth === 0 && (
              <button
                type="button"
                className="flex items-center gap-1.5 font-[family-name:var(--font-manrope)] hover:text-[#582FF5] transition-colors"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="18 15 12 9 6 15" />
                </svg>
                <span>Прикажи помалку</span>
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Render sub-replies recursively */}
      {comment.replies && comment.replies.length > 0 && (
        <div className="relative mt-4">
          {/* Connector line for nested replies */}
          <div className="absolute left-5 top-0 bottom-0 w-0.5 bg-[#E6E6E6]/60" />
          <div className="flex flex-col gap-4">
            {comment.replies.map((reply) => (
              <CommentNode
                key={reply.id}
                comment={reply}
                getInitials={getInitials}
                depth={depth + 1}
              />
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
