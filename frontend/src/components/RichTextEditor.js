"use client";

import { useEffect, useRef, useState } from "react";
import { getMarkRange } from "@tiptap/core";
import { EditorContent, useEditor, useEditorState } from "@tiptap/react";
import Link from "@tiptap/extension-link";
import StarterKit from "@tiptap/starter-kit";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faAt,
  faBold,
  faCode,
  faItalic,
  faLink,
  faListOl,
  faListUl,
  faQuoteLeft,
} from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;

const MAX_DESCRIPTION_LENGTH = 1000;

const TOOLBAR_BUTTONS = [
  {
    key: "bold",
    label: "Bold",
    icon: faBold,
    onClick: (editor) => editor.chain().focus().toggleBold().run(),
  },
  {
    key: "italic",
    label: "Italic",
    icon: faItalic,
    onClick: (editor) => editor.chain().focus().toggleItalic().run(),
  },
  {
    key: "bulletList",
    label: "Bullet list",
    icon: faListUl,
    onClick: (editor) => editor.chain().focus().toggleBulletList().run(),
  },
  {
    key: "orderedList",
    label: "Ordered list",
    icon: faListOl,
    onClick: (editor) => editor.chain().focus().toggleOrderedList().run(),
  },
  {
    key: "link",
    label: "Link",
    icon: faLink,
  },
  {
    key: "codeBlock",
    label: "Code block",
    icon: faCode,
    onClick: (editor) => editor.chain().focus().toggleCodeBlock().run(),
  },
  {
    key: "quote",
    label: "Наводници",
    icon: faQuoteLeft,
    onClick: (editor) => {
      const { from, to, empty } = editor.state.selection;

      if (empty) {
        editor.chain().focus().insertContent("„“").run();
        editor.commands.setTextSelection(editor.state.selection.from - 1);
        return;
      }

      editor.chain().focus().insertContentAt(to, "“").insertContentAt(from, "„").run();
    },
  },
  {
    key: "mention",
    label: "Спомни корисник",
    icon: faAt,
  },
];

function ToolbarButton({ editor, button, active, onClick }) {
  return (
    <button
      type="button"
      aria-label={button.label}
      aria-pressed={active}
      onClick={() => (onClick ?? button.onClick)(editor)}
      className={`flex size-9 items-center justify-center rounded-lg text-[16px] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] ${
        active
          ? "bg-[#CFE9ED] text-black"
          : "text-[#595959] hover:bg-[#DCEBED] hover:text-black"
      }`}
    >
      <FontAwesomeIcon icon={button.icon} className="h-4 w-4" />
    </button>
  );
}

function getCurrentLinkInfo(editor) {
  const { state } = editor;
  const range = getMarkRange(state.selection.$from, state.schema.marks.link);

  if (range) {
    return {
      initialText: state.doc.textBetween(range.from, range.to),
      initialUrl: editor.getAttributes("link").href ?? "",
    };
  }

  const { from, to, empty } = state.selection;
  return { initialText: empty ? "" : state.doc.textBetween(from, to), initialUrl: "" };
}

function findPosAtTextOffset(doc, targetOffset) {
  let consumed = 0;
  let resultPos = doc.content.size;

  doc.descendants((node, pos) => {
    if (consumed >= targetOffset) return false;
    if (!node.isText) return true;

    if (consumed + node.text.length >= targetOffset) {
      resultPos = pos + (targetOffset - consumed);
      consumed = targetOffset;
      return false;
    }

    consumed += node.text.length;
    return true;
  });

  return resultPos;
}

function applyLink(editor, text, rawUrl) {
  const trimmedUrl = rawUrl.trim();

  if (!trimmedUrl) {
    editor.chain().focus().extendMarkRange("link").unsetLink().run();
    return;
  }

  const href = /^https?:\/\//i.test(trimmedUrl) ? trimmedUrl : `https://${trimmedUrl}`;
  const label = text.trim() || href;

  editor
    .chain()
    .focus()
    .extendMarkRange("link")
    .insertContent({ type: "text", text: label, marks: [{ type: "link", attrs: { href } }] })
    .run();
}

function LinkPopover({ initialText, initialUrl, onSubmit, onClose }) {
  const [text, setText] = useState(initialText);
  const [url, setUrl] = useState(initialUrl);
  const popoverRef = useRef(null);

  useEffect(() => {
    function handlePointerDown(event) {
      if (!popoverRef.current?.contains(event.target)) onClose();
    }

    document.addEventListener("pointerdown", handlePointerDown);
    return () => document.removeEventListener("pointerdown", handlePointerDown);
  }, [onClose]);

  function handleKeyDown(event) {
    if (event.key === "Enter") {
      event.preventDefault();
      onSubmit(text, url);
    }
    if (event.key === "Escape") onClose();
  }

  return (
    <div
      ref={popoverRef}
      className="absolute bottom-full left-0 z-20 mb-2 flex w-64 flex-col gap-2 rounded-xl border border-[#CCCCCC] bg-white p-2 shadow-lg"
    >
      <input
        autoFocus
        type="text"
        value={text}
        onChange={(event) => setText(event.target.value)}
        onKeyDown={handleKeyDown}
        placeholder="Текст на линкот"
        className="w-full font-[family-name:var(--font-manrope)] text-[13px] text-black outline-none placeholder:text-[#595959]"
      />
      <div className="flex items-center gap-2 border-t border-[#CCCCCC] pt-2">
        <input
          type="text"
          value={url}
          onChange={(event) => setUrl(event.target.value)}
          onKeyDown={handleKeyDown}
          placeholder="Внеси линк"
          className="min-w-0 flex-1 font-[family-name:var(--font-manrope)] text-[13px] text-black outline-none placeholder:text-[#595959]"
        />
        <button
          type="button"
          onClick={() => onSubmit(text, url)}
          className="shrink-0 cursor-pointer rounded-lg bg-[#582FF5] px-3 py-1 font-[family-name:var(--font-manrope)] text-[12px] font-bold text-white transition-colors hover:bg-[#4B25E0]"
        >
          Додади
        </button>
      </div>
    </div>
  );
}

// TODO lista na korisnici koga kje ima endpoint - za sega prazna.
function MentionPopover({ onClose }) {
  const popoverRef = useRef(null);

  useEffect(() => {
    function handlePointerDown(event) {
      if (!popoverRef.current?.contains(event.target)) onClose();
    }

    document.addEventListener("pointerdown", handlePointerDown);
    return () => document.removeEventListener("pointerdown", handlePointerDown);
  }, [onClose]);

  return (
    <div
      ref={popoverRef}
      className="absolute bottom-full left-0 z-20 mb-2 w-56 rounded-xl border border-[#CCCCCC] bg-white p-3 shadow-lg"
    >
      <p className="font-[family-name:var(--font-manrope)] text-[13px] text-[#595959]">
        Нема пронајден корисник
      </p>
    </div>
  );
}

export default function RichTextEditor({
  name = "content",
  placeholder = "Напиши сè што сакаш да кажеш...",
  errorMessage,
  onContentChange,
  onBlur,
}) {
  const [html, setHtml] = useState("");
  const [isEmpty, setIsEmpty] = useState(true);
  const [characterCount, setCharacterCount] = useState(0);
  const [linkPopoverOpen, setLinkPopoverOpen] = useState(false);
  const [mentionPopoverOpen, setMentionPopoverOpen] = useState(false);

  function syncEditorState(editor) {
    const nextHtml = editor.getHTML();
    setHtml(nextHtml);
    setIsEmpty(editor.isEmpty);
    setCharacterCount(editor.state.doc.textContent.length);
    onContentChange?.(nextHtml, editor.isEmpty);
  }

  const editor = useEditor({
    immediatelyRender: false,
    extensions: [
      StarterKit.configure({
        heading: false,
        horizontalRule: false,
        link: false,
      }),
      Link.configure({
        openOnClick: false,
        autolink: true,
        defaultProtocol: "https",
        HTMLAttributes: {
          class: "cursor-pointer text-[#A6E4ED] underline underline-offset-2",
          rel: "noopener noreferrer",
          target: "_blank",
        },
      }),
    ],
    content: "",
    editorProps: {
      attributes: {
        "aria-label": "Содржина на дискусијата",
        class:
          "min-h-[195px] w-full px-4 pb-8 pt-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-black outline-none",
      },
      handleClickOn(view, pos, node, nodePos, event) {
        const anchor = event.target instanceof HTMLElement ? event.target.closest("a") : null;
        if (anchor?.href) {
          window.open(anchor.href, "_blank", "noopener,noreferrer");
          return true;
        }
        return false;
      },
      handleTextInput(view, from, to, text) {
        const currentLength = view.state.doc.textContent.length;
        const selectedLength = view.state.doc.textBetween(from, to).length;
        return currentLength - selectedLength + text.length > MAX_DESCRIPTION_LENGTH;
      },
      handlePaste(view, event) {
        const pastedText = event.clipboardData?.getData("text/plain") ?? "";
        const currentLength = view.state.doc.textContent.length;
        const { from, to } = view.state.selection;
        const selectedLength = view.state.doc.textBetween(from, to).length;
        const availableLength = MAX_DESCRIPTION_LENGTH - (currentLength - selectedLength);

        if (pastedText.length <= availableLength) return false;
        if (availableLength <= 0) return true;

        view.dispatch(view.state.tr.insertText(pastedText.slice(0, availableLength), from, to));
        return true;
      },
    },
    onCreate: ({ editor }) => {
      syncEditorState(editor);
    },
    onUpdate: ({ editor }) => {
      const { doc } = editor.state;
      if (doc.textContent.length > MAX_DESCRIPTION_LENGTH) {
        const cutPos = findPosAtTextOffset(doc, MAX_DESCRIPTION_LENGTH);
        editor.chain().deleteRange({ from: cutPos, to: doc.content.size }).run();
        return;
      }
      syncEditorState(editor);
    },
    onSelectionUpdate: ({ editor }) => {
      setIsEmpty(editor.isEmpty);
    },
    onBlur: ({ editor }) => {
      onBlur?.(editor.getHTML(), editor.isEmpty);
    },
  });
  const activeStates = useEditorState({
    editor,
    selector: ({ editor }) => ({
      bold: editor?.isActive("bold") ?? false,
      italic: editor?.isActive("italic") ?? false,
      bulletList: editor?.isActive("bulletList") ?? false,
      orderedList: editor?.isActive("orderedList") ?? false,
      link: editor?.isActive("link") ?? false,
      codeBlock: editor?.isActive("codeBlock") ?? false,
    }),
  });
  const counterTextColor =
    characterCount >= MAX_DESCRIPTION_LENGTH ? "text-[var(--color-error)]" : "text-[#595959]";

  return (
    <section className="relative flex w-[632px] max-w-full flex-col" aria-label="Уредник за содржина">
      <input type="hidden" name={name} value={html} />
      <div
        className={`overflow-hidden rounded-[13px] border bg-white ${
          errorMessage ? "border-[var(--color-error)]" : "border-[#D9D9D9]"
        }`}
      >
        <div className="relative min-h-[195px]">
          {isEmpty && (
            <span className="pointer-events-none absolute left-4 top-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-[#595959]">
              {placeholder}
            </span>
          )}
          <EditorContent
            editor={editor}
            aria-describedby={errorMessage ? `${name}-error` : undefined}
            className="min-h-[195px] [&_.ProseMirror_blockquote]:border-l-2 [&_.ProseMirror_blockquote]:border-[#CCCCCC] [&_.ProseMirror_blockquote]:pl-3 [&_.ProseMirror_code]:rounded [&_.ProseMirror_code]:bg-[#E5E5E5] [&_.ProseMirror_code]:px-1 [&_.ProseMirror_code]:py-0.5 [&_.ProseMirror_pre]:overflow-x-auto [&_.ProseMirror_pre]:rounded-lg [&_.ProseMirror_pre]:bg-[#E5E5E5] [&_.ProseMirror_pre]:p-3 [&_.ProseMirror_pre]:font-mono [&_.ProseMirror_pre]:text-[13px] [&_.ProseMirror_pre_code]:bg-transparent [&_.ProseMirror_pre_code]:p-0 [&_.ProseMirror_ol]:list-decimal [&_.ProseMirror_ol]:pl-6 [&_.ProseMirror_p]:my-0 [&_.ProseMirror_ul]:list-disc [&_.ProseMirror_ul]:pl-6"
          />
        </div>

        <div className="flex min-h-16 items-center justify-between border-t border-[#D9D9D9] px-4 py-3">
          <div className="flex flex-wrap items-center gap-1.5">
            {editor &&
              TOOLBAR_BUTTONS.map((button) => {
                if (button.key === "link") {
                  return (
                    <div key={button.key} className="relative">
                      <ToolbarButton
                        editor={editor}
                        button={button}
                        active={activeStates?.link ?? false}
                        onClick={() => {
                          setMentionPopoverOpen(false);
                          setLinkPopoverOpen((prev) => !prev);
                        }}
                      />
                      {linkPopoverOpen && (
                        <LinkPopover
                          {...getCurrentLinkInfo(editor)}
                          onSubmit={(text, url) => {
                            applyLink(editor, text, url);
                            setLinkPopoverOpen(false);
                          }}
                          onClose={() => setLinkPopoverOpen(false)}
                        />
                      )}
                    </div>
                  );
                }

                if (button.key === "mention") {
                  return (
                    <div key={button.key} className="relative">
                      <ToolbarButton
                        editor={editor}
                        button={button}
                        active={mentionPopoverOpen}
                        onClick={() => {
                          setLinkPopoverOpen(false);
                          setMentionPopoverOpen((prev) => !prev);
                        }}
                      />
                      {mentionPopoverOpen && (
                        <MentionPopover onClose={() => setMentionPopoverOpen(false)} />
                      )}
                    </div>
                  );
                }

                return (
                  <ToolbarButton
                    key={button.key}
                    editor={editor}
                    button={button}
                    active={activeStates?.[button.key] ?? false}
                  />
                );
              })}
          </div>
          <span
            className={`shrink-0 font-[family-name:var(--font-manrope)] text-[12px] leading-none ${counterTextColor}`}
          >
            {characterCount}/{MAX_DESCRIPTION_LENGTH}
          </span>
        </div>
      </div>
      <p
        id={`${name}-error`}
        className={`absolute left-0 top-full mt-1 w-full truncate font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)] ${
          errorMessage ? "" : "invisible"
        }`}
      >
        {errorMessage || "Нема грешка"}
      </p>
    </section>
  );
}
