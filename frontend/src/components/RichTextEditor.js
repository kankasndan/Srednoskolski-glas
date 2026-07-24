"use client";

import { useState } from "react";
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
    onClick: (editor) => {
      const previousUrl = editor.getAttributes("link").href ?? "";
      const nextUrl = window.prompt("Внеси линк", previousUrl);

      if (nextUrl === null) return;

      const trimmedUrl = nextUrl.trim();
      if (!trimmedUrl) {
        editor.chain().focus().extendMarkRange("link").unsetLink().run();
        return;
      }

      const href = /^https?:\/\//i.test(trimmedUrl) ? trimmedUrl : `https://${trimmedUrl}`;
      editor.chain().focus().extendMarkRange("link").setLink({ href }).run();
    },
  },
  {
    key: "code",
    label: "Inline code",
    icon: faCode,
    onClick: (editor) => editor.chain().focus().toggleCode().run(),
  },
  {
    key: "blockquote",
    label: "Blockquote",
    icon: faQuoteLeft,
    onClick: (editor) => editor.chain().focus().toggleBlockquote().run(),
  },
  {
    key: "mention",
    label: "Mention",
    icon: faAt,
    onClick: (editor) => {
      if (editor.state.doc.textContent.length >= MAX_DESCRIPTION_LENGTH) return;
      editor.chain().focus().insertContent("@").run();
    },
  },
];

function ToolbarButton({ editor, button, active }) {
  return (
    <button
      type="button"
      aria-label={button.label}
      aria-pressed={active}
      onClick={() => button.onClick(editor)}
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

  function syncEditorState(editor) {
    const nextHtml = editor.getHTML();
    setHtml(nextHtml);
    setIsEmpty(editor.isEmpty);
    setCharacterCount(editor.getText().length);
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
          class: "text-[#A6E4ED] underline underline-offset-2",
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
      code: editor?.isActive("code") ?? false,
      blockquote: editor?.isActive("blockquote") ?? false,
      mention: false,
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
            className="min-h-[195px] [&_.ProseMirror_blockquote]:border-l-2 [&_.ProseMirror_blockquote]:border-[#CCCCCC] [&_.ProseMirror_blockquote]:pl-3 [&_.ProseMirror_code]:rounded [&_.ProseMirror_code]:bg-[#E5E5E5] [&_.ProseMirror_code]:px-1 [&_.ProseMirror_code]:py-0.5 [&_.ProseMirror_ol]:list-decimal [&_.ProseMirror_ol]:pl-6 [&_.ProseMirror_p]:my-0 [&_.ProseMirror_ul]:list-disc [&_.ProseMirror_ul]:pl-6"
          />
          <span
            className={`pointer-events-none absolute bottom-3 right-4 font-[family-name:var(--font-manrope)] text-[12px] leading-none ${counterTextColor}`}
          >
            {characterCount}/{MAX_DESCRIPTION_LENGTH}
          </span>
        </div>

        <div className="flex min-h-16 items-center border-t border-[#D9D9D9] px-4 py-3">
          <div className="flex flex-wrap items-center gap-1.5">
            {editor &&
              TOOLBAR_BUTTONS.map((button) => (
                <ToolbarButton
                  key={button.key}
                  editor={editor}
                  button={button}
                  active={activeStates?.[button.key] ?? false}
                />
              ))}
          </div>
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
