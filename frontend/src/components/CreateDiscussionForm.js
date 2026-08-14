"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faAt,
  faBold,
  faChartBar,
  faCode,
  faChevronLeft,
  faFileArrowUp,
  faImage,
  faItalic,
  faLink,
  faListOl,
  faListUl,
  faQuoteLeft,
  faVideo,
  faXmark,
} from "@fortawesome/free-solid-svg-icons";
import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useRef, useState } from "react";
import { FORUMS } from "@/lib/forums";
import { filterMentionUsers, getActiveMention, splitMentionText } from "@/lib/mentions";
import { saveUserThread } from "@/lib/userThreads";

config.autoAddCss = false;

const ATTACHMENT_TYPES = {
  image: {
    label: "Прикачи слика",
    icon: faImage,
    panelPlaceholder: "Прикачи слика тука",
    variant: "center",
    accept: "image/*",
  },
  document: {
    label: "Прикачи документ",
    icon: faFileArrowUp,
    panelPlaceholder: "Прикачи документ тука",
    variant: "bar",
    accept: ".pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx",
  },
  poll: {
    label: "Направи анкета",
    icon: faChartBar,
    panelPlaceholder: "Направи анкета тука",
    variant: "center",
  },
  video: {
    label: "Прикачи видео линк",
    icon: faVideo,
    panelPlaceholder: "Внеси видео линк тука",
    variant: "bar",
  },
};

const NUMBERED_LINE_REGEX = /^(\d+)\.\s(.*)$/;

function getLineBounds(text, position) {
  const lineStart = text.lastIndexOf("\n", position - 1) + 1;
  const nextNewline = text.indexOf("\n", position);
  const lineEnd = nextNewline === -1 ? text.length : nextNewline;

  return {
    lineStart,
    lineEnd,
    line: text.slice(lineStart, lineEnd),
  };
}

function stripNumberPrefix(line) {
  return line.replace(/^\d+\.\s/, "");
}

function isNumberedLine(line) {
  return NUMBERED_LINE_REGEX.test(line);
}

function getContinuingListNumber(linesBeforeCurrentLine) {
  for (let index = linesBeforeCurrentLine.length - 1; index >= 0; index -= 1) {
    const line = linesBeforeCurrentLine[index];

    if (line.trim() === "") {
      if (index === linesBeforeCurrentLine.length - 1) {
        continue;
      }
      break;
    }

    const match = line.match(/^(\d+)\.\s/);
    if (match) {
      return Number.parseInt(match[1], 10) + 1;
    }

    break;
  }

  return 1;
}

function findNumberedBlockBounds(lines, lineIndex) {
  let blockStart = lineIndex;

  while (blockStart > 0) {
    const previousLine = lines[blockStart - 1];
    if (previousLine.trim() === "") break;
    if (!isNumberedLine(previousLine)) break;
    blockStart -= 1;
  }

  let blockEnd = lineIndex;

  while (blockEnd < lines.length - 1) {
    const nextLine = lines[blockEnd + 1];
    if (nextLine.trim() === "") break;
    if (!isNumberedLine(nextLine)) break;
    blockEnd += 1;
  }

  return { blockStart, blockEnd };
}

function renumberNumberedBlock(lines, lineIndex) {
  const { blockStart, blockEnd } = findNumberedBlockBounds(lines, lineIndex);

  for (let index = blockStart, number = 1; index <= blockEnd; index += 1, number += 1) {
    const content = stripNumberPrefix(lines[index]);
    lines[index] = `${number}. ${content}`;
  }
}

function applyNumberedListFormat(body, start, end) {
  const selected = body.slice(start, end);
  const lines = body.split("\n");
  const startLineIndex = body.slice(0, start).split("\n").length - 1;
  const endLineIndex = body.slice(0, end).split("\n").length - 1;

  if (selected.includes("\n")) {
    for (let index = startLineIndex; index <= endLineIndex; index += 1) {
      const content = stripNumberPrefix(lines[index]).trim() || "ставка";
      lines[index] = `${index - startLineIndex + 1}. ${content}`;
    }
  } else {
    const { line } = getLineBounds(body, start);
    const content = selected
      ? stripNumberPrefix(selected)
      : stripNumberPrefix(line).trim() || "ставка";
    const listNumber = getContinuingListNumber(lines.slice(0, startLineIndex));

    lines[startLineIndex] = `${listNumber}. ${content}`;
  }

  renumberNumberedBlock(lines, startLineIndex);

  const nextBody = lines.join("\n");
  let cursorPos = 0;

  for (let index = 0; index <= startLineIndex; index += 1) {
    if (index === startLineIndex) {
      cursorPos += lines[index].length;
      break;
    }

    cursorPos += lines[index].length + 1;
  }

  return { nextBody, selectionStart: cursorPos, selectionEnd: cursorPos };
}

function continueNumberedListOnEnter(body, start, end) {
  const { lineStart, line } = getLineBounds(body, start);
  const match = line.match(NUMBERED_LINE_REGEX);

  if (!match) {
    return null;
  }

  const prefix = `${match[1]}. `;
  const itemText = match[2];
  const cursorInItem = start - lineStart - prefix.length;

  if (itemText.trim() === "" && cursorInItem <= 0) {
    const lines = body.split("\n");
    const lineIndex = body.slice(0, start).split("\n").length - 1;
    lines[lineIndex] = "";

    const nextBody = lines.join("\n");

    return { nextBody, selectionStart: lineStart, selectionEnd: lineStart };
  }

  const nextNumber = Number.parseInt(match[1], 10) + 1;
  const insert = `\n${nextNumber}. `;
  const nextBody = `${body.slice(0, start)}${insert}${body.slice(end)}`;
  const lines = nextBody.split("\n");
  const newLineIndex = body.slice(0, start).split("\n").length;

  renumberNumberedBlock(lines, newLineIndex);

  const normalizedBody = lines.join("\n");
  let cursorPos = 0;

  for (let index = 0; index < newLineIndex; index += 1) {
    cursorPos += lines[index].length + 1;
  }

  const newLine = lines[newLineIndex];
  const prefixMatch = newLine.match(/^(\d+)\.\s/);
  cursorPos += prefixMatch ? prefixMatch[0].length : 0;

  return {
    nextBody: normalizedBody,
    selectionStart: cursorPos,
    selectionEnd: cursorPos,
  };
}

const toolbarButtons = [
  { icon: faBold, label: "Bold", action: "bold" },
  { icon: faItalic, label: "Italic", action: "italic" },
  { icon: faListOl, label: "Numbered list", action: "numbered" },
  { icon: faListUl, label: "Bullet list", action: "bullet" },
  { icon: faLink, label: "Link", action: "link" },
  { icon: faCode, label: "Code", action: "code" },
  { icon: faQuoteLeft, iconSrc: "/icons/create/quote.png", label: "Quote", action: "quote" },
  { icon: faAt, label: "Mention", action: "mention" },
];

function FieldLabel({ children, htmlFor }) {
  return (
    <label
      htmlFor={htmlFor}
      className="mb-2 block font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-[#0A0A0A]"
    >
      <span className="text-[#FF4D4D]">*</span>
      {children}
    </label>
  );
}

function MentionHighlightLayer({ text }) {
  if (!text) {
    return null;
  }

  return splitMentionText(text).map((part, index) => {
    if (part.type === "mention") {
      return (
        <span
          key={`${part.value}-${index}`}
          className="rounded-sm bg-[#582FF5]/15 font-bold text-[#582FF5]"
        >
          {part.value}
        </span>
      );
    }

    return (
      <span key={`text-${index}`} className="text-[#0A0A0A]">
        {part.value}
      </span>
    );
  });
}

function MentionSuggestions({
  users,
  activeIndex,
  onSelect,
  onHover,
}) {
  if (users.length === 0) {
    return (
      <div className="absolute bottom-4 left-4 right-4 z-20 rounded-xl border border-[#CCCCCC] bg-white px-4 py-3 shadow-lg">
        <p className="font-[family-name:var(--font-manrope)] text-[13px] text-[#595959]">
          Нема пронајдени корисници
        </p>
      </div>
    );
  }

  return (
    <div className="absolute bottom-4 left-4 right-4 z-20 max-h-48 overflow-y-auto rounded-xl border border-[#CCCCCC] bg-white py-1 shadow-lg">
      {users.map((user, index) => (
        <button
          key={user.username}
          type="button"
          onMouseDown={(event) => event.preventDefault()}
          onMouseEnter={() => onHover(index)}
          onClick={() => onSelect(user.username)}
          className={`flex w-full items-center gap-3 px-4 py-2 text-left transition-colors ${
            index === activeIndex ? "bg-[#F5F5F5]" : "hover:bg-[#F5F5F5]"
          }`}
        >
          <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#582FF5] font-[family-name:var(--font-manrope)] text-[11px] font-bold text-white">
            {user.username.slice(0, 2).toUpperCase()}
          </div>
          <div className="min-w-0">
            <p className="truncate font-[family-name:var(--font-manrope)] text-[14px] font-bold text-[#0A0A0A]">
              @{user.username}
            </p>
            {user.school && (
              <p className="truncate font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
                {user.school}
              </p>
            )}
          </div>
        </button>
      ))}
    </div>
  );
}

function AttachmentPanel({
  type,
  onClose,
  imagePreview,
  imageName,
  onImageSelect,
  documentName,
  onDocumentSelect,
  videoLink,
  onVideoLinkChange,
  pollQuestion,
  onPollQuestionChange,
  pollOptions,
  onPollOptionChange,
  onAddPollOption,
  onRemovePollOption,
}) {
  const { panelPlaceholder, icon, variant, accept } = ATTACHMENT_TYPES[type];
  const fileInputRef = useRef(null);

  function openFilePicker() {
    fileInputRef.current?.click();
  }

  if (type === "poll") {
    return (
      <div className="relative flex w-full flex-col gap-3 rounded-xl border border-[#CCCCCC] bg-white p-4">
        <button
          type="button"
          onClick={onClose}
          aria-label="Затвори"
          className="absolute right-4 top-4 flex size-8 items-center justify-center text-[#595959] transition-colors hover:text-[#0A0A0A]"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>

        <div className="flex items-center gap-3 pr-10 text-[#595959]">
          <FontAwesomeIcon icon={icon} className="h-5 w-5 shrink-0" />
          <span className="font-[family-name:var(--font-manrope)] text-[14px]">
            {panelPlaceholder}
          </span>
        </div>

        <input
          type="text"
          value={pollQuestion}
          onChange={(event) => onPollQuestionChange(event.target.value)}
          placeholder="Прашање за анкетата"
          className="h-11 w-full rounded-xl border border-[#CCCCCC] px-4 font-[family-name:var(--font-manrope)] text-[14px] text-[#0A0A0A] placeholder:text-[#595959] focus:border-[#582FF5] focus:outline-none"
        />

        <div className="flex flex-col gap-2">
          {pollOptions.map((option, index) => (
            <div key={index} className="flex items-center gap-2">
              <input
                type="text"
                value={option}
                onChange={(event) => onPollOptionChange(index, event.target.value)}
                placeholder={`Опција ${index + 1}`}
                className="h-10 min-w-0 flex-1 rounded-xl border border-[#CCCCCC] px-4 font-[family-name:var(--font-manrope)] text-[14px] text-[#0A0A0A] placeholder:text-[#595959] focus:border-[#582FF5] focus:outline-none"
              />
              {pollOptions.length > 2 && (
                <button
                  type="button"
                  onClick={() => onRemovePollOption(index)}
                  aria-label={`Отстрани опција ${index + 1}`}
                  className="flex size-8 shrink-0 items-center justify-center text-[#595959] hover:text-[#0A0A0A]"
                >
                  <FontAwesomeIcon icon={faXmark} className="h-3 w-3" />
                </button>
              )}
            </div>
          ))}
        </div>

        {pollOptions.length < 6 && (
          <button
            type="button"
            onClick={onAddPollOption}
            className="w-fit font-[family-name:var(--font-manrope)] text-[13px] text-[#582FF5] hover:text-[#4B25E0]"
          >
            + Додај опција
          </button>
        )}
      </div>
    );
  }

  if (variant === "bar") {
    const isVideo = type === "video";

    return (
      <div className="relative flex h-14 w-full items-center gap-3 rounded-xl border border-[#CCCCCC] bg-white px-4">
        <input
          ref={fileInputRef}
          type="file"
          accept={accept}
          className="hidden"
          onChange={onDocumentSelect}
        />

        <FontAwesomeIcon icon={icon} className="h-5 w-5 shrink-0 text-[#595959]" />

        {isVideo ? (
          <input
            type="url"
            value={videoLink}
            onChange={(event) => onVideoLinkChange(event.target.value)}
            placeholder={panelPlaceholder}
            className="min-w-0 flex-1 bg-transparent font-[family-name:var(--font-manrope)] text-[14px] text-[#0A0A0A] placeholder:text-[#595959] focus:outline-none"
          />
        ) : (
          <button
            type="button"
            onClick={openFilePicker}
            className="min-w-0 flex-1 truncate text-left font-[family-name:var(--font-manrope)] text-[14px] text-[#595959] hover:text-[#0A0A0A]"
          >
            {documentName || panelPlaceholder}
          </button>
        )}

        <button
          type="button"
          onClick={onClose}
          aria-label="Затвори"
          className="flex size-8 shrink-0 items-center justify-center text-[#595959] transition-colors hover:text-[#0A0A0A]"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>
      </div>
    );
  }

  return (
    <div className="relative flex min-h-[120px] w-full items-center justify-center rounded-xl border border-[#CCCCCC] bg-white">
      <input
        ref={fileInputRef}
        type="file"
        accept={accept}
        className="hidden"
        onChange={onImageSelect}
      />

      <button
        type="button"
        onClick={onClose}
        aria-label="Затвори"
        className="absolute right-4 top-4 flex size-8 items-center justify-center text-[#595959] transition-colors hover:text-[#0A0A0A]"
      >
        <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
      </button>

      <button
        type="button"
        onClick={openFilePicker}
        className="flex h-full w-full flex-col items-center justify-center gap-3 px-4 py-6 text-[#595959] transition-colors hover:text-[#0A0A0A]"
      >
        {imagePreview ? (
          <>
            <img
              src={imagePreview}
              alt={imageName || "Прикачена слика"}
              className="max-h-24 max-w-full rounded-lg object-contain"
            />
            <span className="max-w-full truncate font-[family-name:var(--font-manrope)] text-[13px]">
              {imageName}
            </span>
            <span className="font-[family-name:var(--font-manrope)] text-[12px] text-[#582FF5]">
              Кликни за нова слика
            </span>
          </>
        ) : (
          <>
            <FontAwesomeIcon icon={icon} className="h-6 w-6" />
            <span className="font-[family-name:var(--font-manrope)] text-[14px]">
              {panelPlaceholder}
            </span>
          </>
        )}
      </button>
    </div>
  );
}

export default function CreateDiscussionForm() {
  const router = useRouter();
  const bodyRef = useRef(null);
  const attachmentPanelRef = useRef(null);
  const quickImageInputRef = useRef(null);
  const quickDocumentInputRef = useRef(null);
  const [forum, setForum] = useState("");
  const [title, setTitle] = useState("");
  const [body, setBody] = useState("");
  const [anonymous, setAnonymous] = useState(false);
  const [activeAttachment, setActiveAttachment] = useState(null);
  const [imageFile, setImageFile] = useState(null);
  const [imagePreview, setImagePreview] = useState(null);
  const [documentFile, setDocumentFile] = useState(null);
  const [videoLink, setVideoLink] = useState("");
  const [pollQuestion, setPollQuestion] = useState("");
  const [pollOptions, setPollOptions] = useState(["", ""]);
  const [isPublishing, setIsPublishing] = useState(false);
  const [publishError, setPublishError] = useState("");
  const [mentionOpen, setMentionOpen] = useState(false);
  const [mentionStart, setMentionStart] = useState(0);
  const [mentionQuery, setMentionQuery] = useState("");
  const [mentionIndex, setMentionIndex] = useState(0);

  const mentionSuggestions = filterMentionUsers(mentionQuery);

  function openAttachment(type) {
    setActiveAttachment(type);

    requestAnimationFrame(() => {
      attachmentPanelRef.current?.scrollIntoView({ behavior: "smooth", block: "nearest" });

      if (type === "image") {
        quickImageInputRef.current?.click();
      }

      if (type === "document") {
        quickDocumentInputRef.current?.click();
      }
    });
  }

  function updateBody(nextBody, selectionStart, selectionEnd) {
    setBody(nextBody);

    requestAnimationFrame(() => {
      const textarea = bodyRef.current;
      if (!textarea) return;
      textarea.focus();
      textarea.setSelectionRange(selectionStart, selectionEnd);

      const activeMention = getActiveMention(nextBody, selectionStart);
      if (activeMention) {
        setMentionOpen(true);
        setMentionStart(activeMention.start);
        setMentionQuery(activeMention.query);
      } else {
        setMentionOpen(false);
        setMentionQuery("");
      }
    });
  }

  function openMentionAt(start, query = "") {
    setMentionOpen(true);
    setMentionStart(start);
    setMentionQuery(query);
    setMentionIndex(0);
  }

  function closeMention() {
    setMentionOpen(false);
    setMentionQuery("");
    setMentionIndex(0);
  }

  function selectMention(username) {
    const textarea = bodyRef.current;
    if (!textarea) return;

    const cursor = textarea.selectionStart;
    const before = body.slice(0, mentionStart);
    const after = body.slice(cursor);
    const mentionText = `@${username} `;
    const nextBody = `${before}${mentionText}${after}`;
    const cursorPos = mentionStart + mentionText.length;

    closeMention();
    updateBody(nextBody, cursorPos, cursorPos);
  }

  function handleBodyChange(event) {
    const nextBody = event.target.value;
    const cursor = event.target.selectionStart;

    setBody(nextBody);

    const activeMention = getActiveMention(nextBody, cursor);
    if (activeMention) {
      setMentionOpen(true);
      setMentionStart(activeMention.start);
      setMentionQuery(activeMention.query);
      setMentionIndex(0);
      return;
    }

    closeMention();
  }

  function applyFormat(action) {
    const textarea = bodyRef.current;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = body.slice(start, end);

    if (action === "bold") {
      const text = selected || "текст";
      updateBody(`${body.slice(0, start)}**${text}**${body.slice(end)}`, start + 2, start + 2 + text.length);
      return;
    }

    if (action === "italic") {
      const text = selected || "текст";
      updateBody(`${body.slice(0, start)}*${text}*${body.slice(end)}`, start + 1, start + 1 + text.length);
      return;
    }

    if (action === "code") {
      const text = selected || "код";
      updateBody(`${body.slice(0, start)}\`${text}\`${body.slice(end)}`, start + 1, start + 1 + text.length);
      return;
    }

    if (action === "link") {
      const url = window.prompt("Внеси линк (URL):");
      if (!url) return;
      const text = selected || "линк";
      updateBody(
        `${body.slice(0, start)}[${text}](${url})${body.slice(end)}`,
        start + 1,
        start + 1 + text.length,
      );
      return;
    }

    if (action === "mention") {
      const text = selected.trim();

      if (text) {
        const mentionText = `@${text} `;
        updateBody(
          `${body.slice(0, start)}${mentionText}${body.slice(end)}`,
          start + mentionText.length,
          start + mentionText.length,
        );
        return;
      }

      const mentionText = "@";
      updateBody(
        `${body.slice(0, start)}${mentionText}${body.slice(end)}`,
        start + mentionText.length,
        start + mentionText.length,
      );
      openMentionAt(start, "");
      return;
    }

    if (action === "quote") {
      const text = selected || "цитат";
      const quoted = text
        .split("\n")
        .map((line) => `> ${line}`)
        .join("\n");
      updateBody(`${body.slice(0, start)}${quoted}${body.slice(end)}`, start, start + quoted.length);
      return;
    }

    if (action === "bullet") {
      const text = selected || "ставка";
      const list = text
        .split("\n")
        .map((line) => (line.startsWith("- ") ? line : `- ${line}`))
        .join("\n");
      updateBody(`${body.slice(0, start)}${list}${body.slice(end)}`, start, start + list.length);
      return;
    }

    if (action === "numbered") {
      const { nextBody, selectionStart, selectionEnd } = applyNumberedListFormat(body, start, end);
      updateBody(nextBody, selectionStart, selectionEnd);
    }
  }

  function handleBodyKeyDown(event) {
    if (mentionOpen && mentionSuggestions.length > 0) {
      if (event.key === "ArrowDown") {
        event.preventDefault();
        setMentionIndex((current) => (current + 1) % mentionSuggestions.length);
        return;
      }

      if (event.key === "ArrowUp") {
        event.preventDefault();
        setMentionIndex(
          (current) => (current - 1 + mentionSuggestions.length) % mentionSuggestions.length,
        );
        return;
      }

      if (event.key === "Enter" || event.key === "Tab") {
        event.preventDefault();
        selectMention(mentionSuggestions[mentionIndex].username);
        return;
      }
    }

    if (mentionOpen && event.key === "Escape") {
      event.preventDefault();
      closeMention();
      return;
    }

    if (event.key !== "Enter") return;

    const textarea = bodyRef.current;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    if (start !== end) return;

    const result = continueNumberedListOnEnter(body, start, end);
    if (!result) return;

    event.preventDefault();
    updateBody(result.nextBody, result.selectionStart, result.selectionEnd);
  }

  function closeAttachment() {
    setActiveAttachment(null);
  }

  function handleImageSelect(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    if (imagePreview) {
      URL.revokeObjectURL(imagePreview);
    }

    setImageFile(file);
    setImagePreview(URL.createObjectURL(file));
    event.target.value = "";
  }

  function handleDocumentSelect(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    setDocumentFile(file);
    event.target.value = "";
  }

  function handlePollOptionChange(index, value) {
    setPollOptions((current) =>
      current.map((option, optionIndex) => (optionIndex === index ? value : option)),
    );
  }

  function addPollOption() {
    setPollOptions((current) => (current.length < 6 ? [...current, ""] : current));
  }

  function removePollOption(index) {
    setPollOptions((current) =>
      current.length > 2 ? current.filter((_, optionIndex) => optionIndex !== index) : current,
    );
  }

  async function handlePublish(event) {
    event.preventDefault();
    setPublishError("");

    if (!forum) {
      setPublishError("Избери форум.");
      return;
    }

    if (!title.trim()) {
      setPublishError("Внеси наслов на дискусијата.");
      return;
    }

    if (!body.trim()) {
      setPublishError("Напиши ја содржината на дискусијата.");
      return;
    }

    setIsPublishing(true);

    try {
      const thread = await saveUserThread({
        forum,
        title,
        body,
        anonymous,
        imageFile,
        documentFile,
        videoLink,
        pollQuestion,
        pollOptions,
      });

      router.push(`/p/${thread.forumSlug}`);
    } catch {
      setPublishError("Не успеавме да ја објавиме дискусијата. Обиди се повторно.");
      setIsPublishing(false);
    }
  }

  const visibleAttachments = Object.entries(ATTACHMENT_TYPES).filter(
    ([type]) => type !== activeAttachment,
  );

  return (
    <div className="flex w-full max-w-[1440px] flex-col px-14 pb-16 pt-8">
      <Link
        href="/feed"
        prefetch={false}
        className="mb-8 flex w-fit items-center gap-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal text-[#595959] no-underline transition-colors hover:text-[#0A0A0A]"
      >
        <FontAwesomeIcon icon={faChevronLeft} className="h-3 w-3" />
        Назад
      </Link>

      <div className="flex items-start justify-between gap-12">
        <form onSubmit={handlePublish} className="flex w-[580px] shrink-0 flex-col gap-6">
          <div>
            <FieldLabel htmlFor="forum">Каде сакаш да започнеш дискусија?</FieldLabel>
            <div className="relative">
              <select
                id="forum"
                name="forum"
                required
                value={forum}
                onChange={(event) => setForum(event.target.value)}
                className={`h-12 w-full appearance-none rounded-xl border border-[#CCCCCC] bg-white px-5 py-2 pr-12 font-[family-name:var(--font-manrope)] text-[14px] focus:border-[#582FF5] focus:outline-none ${
                  forum ? "text-[#0A0A0A]" : "text-[#595959]"
                }`}
              >
                <option value="" disabled className="text-[#595959]">
                  Избери форум
                </option>
                {FORUMS.map(({ name }) => (
                  <option key={name} value={name} className="text-[#0A0A0A]">
                    {name}
                  </option>
                ))}
              </select>
              <img
                src="/chevron-down.svg"
                alt=""
                aria-hidden="true"
                className="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2"
              />
            </div>
          </div>

          <div>
            <FieldLabel htmlFor="title">Наслов</FieldLabel>
            <div className="relative">
              <input
                id="title"
                name="title"
                type="text"
                required
                maxLength={200}
                value={title}
                onChange={(event) => setTitle(event.target.value)}
                placeholder="Внеси наслов на дискусијата"
                className="h-12 w-full rounded-xl border border-[#CCCCCC] bg-white px-4 py-2 pr-16 font-[family-name:var(--font-manrope)] text-[14px] text-[#0A0A0A] placeholder:font-bold placeholder:text-[#595959] focus:border-[#582FF5] focus:outline-none"
              />
              <span className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
                {title.length}/200
              </span>
            </div>
          </div>

          <div className="overflow-hidden rounded-xl border border-[#CCCCCC] bg-white">
            <input
              ref={quickImageInputRef}
              type="file"
              accept="image/*"
              className="hidden"
              onChange={handleImageSelect}
            />
            <input
              ref={quickDocumentInputRef}
              type="file"
              accept=".pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx"
              className="hidden"
              onChange={handleDocumentSelect}
            />

            <div className="relative">
              <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 overflow-hidden px-4 py-4 font-[family-name:var(--font-manrope)] text-[14px] leading-relaxed whitespace-pre-wrap break-words"
              >
                <MentionHighlightLayer text={body} />
              </div>

              <textarea
                ref={bodyRef}
                id="body"
                name="body"
                value={body}
                onChange={handleBodyChange}
                onKeyDown={handleBodyKeyDown}
                placeholder="Напиши сè што сакаш да кажеш..."
                className="relative min-h-[220px] w-full resize-none bg-transparent px-4 py-4 font-[family-name:var(--font-manrope)] text-[14px] leading-relaxed text-transparent caret-[#0A0A0A] placeholder:text-[#595959] focus:outline-none selection:bg-[#582FF5]/20"
              />

              {mentionOpen && (
                <MentionSuggestions
                  users={mentionSuggestions}
                  activeIndex={Math.min(mentionIndex, Math.max(mentionSuggestions.length - 1, 0))}
                  onSelect={selectMention}
                  onHover={setMentionIndex}
                />
              )}
            </div>

            <div className="flex items-center justify-between border-t border-[#CCCCCC] px-4 py-3">
              <div className="flex items-center gap-1">
                {toolbarButtons.map(({ icon, iconSrc, label, action }) => (
                  <button
                    key={label}
                    type="button"
                    aria-label={label}
                    onClick={() => applyFormat(action)}
                    className="flex size-8 items-center justify-center rounded-lg text-[#595959] transition-colors hover:bg-[#F5F5F5] hover:text-[#0A0A0A]"
                  >
                    {iconSrc ? (
                      <Image
                        src={iconSrc}
                        alt=""
                        width={14}
                        height={14}
                        className="size-3.5 object-contain opacity-70"
                      />
                    ) : (
                      <FontAwesomeIcon icon={icon} className="h-4 w-4" />
                    )}
                  </button>
                ))}
              </div>

              <button
                type="submit"
                disabled={isPublishing}
                className="flex h-10 items-center justify-center rounded-xl bg-[#582FF5] px-8 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {isPublishing ? "Се објавува..." : "Објави"}
              </button>
            </div>
          </div>

          {publishError && (
            <p className="font-[family-name:var(--font-manrope)] text-[13px] text-[#FF4D4D]">
              {publishError}
            </p>
          )}

          {activeAttachment && (
            <div ref={attachmentPanelRef}>
              <AttachmentPanel
                type={activeAttachment}
                onClose={closeAttachment}
                imagePreview={imagePreview}
                imageName={imageFile?.name}
                onImageSelect={handleImageSelect}
                documentName={documentFile?.name}
                onDocumentSelect={handleDocumentSelect}
                videoLink={videoLink}
                onVideoLinkChange={setVideoLink}
                pollQuestion={pollQuestion}
                onPollQuestionChange={setPollQuestion}
                pollOptions={pollOptions}
                onPollOptionChange={handlePollOptionChange}
                onAddPollOption={addPollOption}
                onRemovePollOption={removePollOption}
              />
            </div>
          )}

          <div
            className={`grid w-full gap-3 ${
              visibleAttachments.length === 4
                ? "grid-cols-4"
                : visibleAttachments.length === 3
                  ? "grid-cols-3"
                  : "grid-cols-2"
            }`}
          >
            {visibleAttachments.map(([type, { label, icon }]) => (
              <button
                key={type}
                type="button"
                onClick={() => openAttachment(type)}
                aria-label={label}
                className="flex h-11 w-full min-w-0 items-center justify-center gap-2 rounded-xl border border-[#CCCCCC] bg-white px-2 font-[family-name:var(--font-manrope)] text-[13px] text-[#595959] transition-colors hover:border-[#582FF5] hover:text-[#0A0A0A]"
              >
                <FontAwesomeIcon icon={icon} className="size-4 shrink-0" />
                <span className="truncate">{label}</span>
              </button>
            ))}
          </div>

          <div className="flex flex-col gap-2">
            <label className="flex cursor-pointer items-center gap-3">
              <input
                type="checkbox"
                checked={anonymous}
                onChange={(event) => setAnonymous(event.target.checked)}
                className="size-4 shrink-0 rounded border border-[#CCCCCC] accent-[#582FF5]"
              />
              <span className="font-[family-name:var(--font-manrope)] text-[14px] text-[#0A0A0A]">
                Објави ја дискусијата анонимно
              </span>
            </label>
            <p className="pl-7 font-[family-name:var(--font-manrope)] text-[12px] leading-relaxed text-[#595959]">
              Објавувањето на оваа дискусија анонимно значи дека твојот псевдоним
              нема да биде видлив на останатите корисници.
            </p>
          </div>
        </form>

        <div className="flex shrink-0 items-start justify-center pt-16">
          <Image
            src="/create-chameleon.png"
            alt=""
            width={395}
            height={366}
            priority
            className="h-[366px] w-[395px] object-contain"
          />
        </div>
      </div>

      <footer className="mt-auto flex flex-col gap-3 pt-20">
        <div className="flex flex-wrap gap-6">
          <Link
            href="#"
            className="font-[family-name:var(--font-manrope)] text-[12px] text-[#595959] no-underline transition-colors hover:text-[#0A0A0A]"
          >
            Услови за користење
          </Link>
          <Link
            href="#"
            className="font-[family-name:var(--font-manrope)] text-[12px] text-[#595959] no-underline transition-colors hover:text-[#0A0A0A]"
          >
            Приватност
          </Link>
          <Link
            href="#"
            className="font-[family-name:var(--font-manrope)] text-[12px] text-[#595959] no-underline transition-colors hover:text-[#0A0A0A]"
          >
            Правила
          </Link>
        </div>
        <div className="font-[family-name:var(--font-manrope)] text-[12px] leading-relaxed text-[#582FF5]">
          <p>© 2026 Средношколски Глас.</p>
          <p>Сите права задржани.</p>
        </div>
      </footer>
    </div>
  );
}
