"use client";

import { useEffect, useRef, useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faLink } from "@fortawesome/free-solid-svg-icons";
import DocumentAttachment from "@/components/compose/DocumentAttachment";
import LinkAttachment from "@/components/compose/LinkAttachment";
import MediaAttachments from "@/components/compose/MediaAttachments";
import PillButton from "@/components/ui/PillButton";
import PollAttachment from "@/components/compose/PollAttachment";

config.autoAddCss = false;

const MAX_PHOTOS = 10;
const MAX_VIDEOS = 1;
/** Must stay under PHP upload_max_filesize and API max:102400 (100 MB). */
const MAX_FILE_BYTES = 100 * 1024 * 1024;
const PHOTO_ACCEPT = ".png,.jpg,.jpeg,.webp,.gif";
const VIDEO_ACCEPT = ".mp4";
const DOCUMENT_ACCEPT = ".pdf,.doc,.docx";

const TYPES = [
  { label: "Слика", icon: "/new thread icons/photo.svg" },
  { label: "Видео", icon: "/new thread icons/video.svg", iconClassName: "h-5" },
  { label: "Датотека", icon: "/new thread icons/documents.svg" },
  { label: "Анкета", icon: "/new thread icons/poll.svg" },
  { label: "Линк", faIcon: faLink },
];

function revokeItem(item) {
  if (item.file) URL.revokeObjectURL(item.url);
}

function fileNameFromUrl(url) {
  try {
    const path = new URL(url).pathname;
    const name = decodeURIComponent(path.split("/").filter(Boolean).pop() || "датотека");
    return name || "датотека";
  } catch {
    return "датотека";
  }
}

function seedFromAttachments(initialAttachments) {
  const mediaItems = (initialAttachments ?? [])
    .filter((item) => item.type === "image" || item.type === "video")
    .map((item) => ({
      id: item.id,
      url: item.url,
      kind: item.type,
      file: null,
    }));

  const existingDoc = (initialAttachments ?? []).find((item) => item.type === "file") ?? null;
  const existingLink = (initialAttachments ?? []).find((item) => item.type === "link") ?? null;

  return {
    mediaItems,
    existingDoc: existingDoc
      ? {
          id: existingDoc.id,
          url: existingDoc.url,
          name: fileNameFromUrl(existingDoc.url),
        }
      : null,
    existingLinkId: existingLink?.id ?? null,
    initialLinkUrl: existingLink?.url ?? "",
    linkValue: existingLink?.url ?? "",
    mediaMode: mediaItems.length > 0,
    docMode: Boolean(existingDoc),
    linkMode: Boolean(existingLink),
  };
}

function appendUniqueId(ids, id) {
  if (id == null || ids.includes(id)) return ids;
  return [...ids, id];
}

function buildAttachmentsPayload({
  mediaItems,
  docFile,
  removedIds,
  linkMode,
  linkValue,
  existingLinkId,
  initialLinkUrl,
  allowPoll,
  pollMode,
  pollData,
  hadInitialPoll,
}) {
  const files = [
    ...mediaItems.map((item) => item.file).filter(Boolean),
    ...(docFile ? [docFile] : []),
  ];

  const removeAttachmentIds = [...removedIds];
  let link = "";

  if (linkMode && linkValue.trim()) {
    const trimmed = linkValue.trim();
    const linkUnchanged = existingLinkId && trimmed === initialLinkUrl;

    if (!linkUnchanged) {
      if (existingLinkId) {
        removeAttachmentIds.push(existingLinkId);
      }
      link = trimmed;
    }
  }

  const poll =
    allowPoll &&
    pollMode &&
    pollData?.question &&
    pollData.options?.length >= 2 &&
    pollData.duration_days
      ? pollData
      : null;

  return {
    files,
    link,
    removeAttachmentIds: [...new Set(removeAttachmentIds)],
    poll,
    removePoll: Boolean(allowPoll && hadInitialPoll && !pollMode),
  };
}

export default function PostTypeButtons({
  widthClassName = "w-[632px]",
  onAttachmentsChange,
  initialAttachments = [],
  initialPoll = null,
  allowPoll = true,
}) {
  const seedRef = useRef(null);
  if (seedRef.current === null) {
    seedRef.current = seedFromAttachments(initialAttachments);
  }
  const seed = seedRef.current;
  const hadInitialPoll = Boolean(initialPoll?.id ?? initialPoll?.question);

  const [selected, setSelected] = useState(null);
  /** Ordered mix of { id?, url, file, kind: "image" | "video" } — order is submitted as-is. */
  const [mediaItems, setMediaItems] = useState(seed.mediaItems);
  const [docFile, setDocFile] = useState(null);
  const [existingDoc, setExistingDoc] = useState(seed.existingDoc);
  const [existingLinkId, setExistingLinkId] = useState(seed.existingLinkId);
  const [initialLinkUrl] = useState(seed.initialLinkUrl);
  const [linkValue, setLinkValue] = useState(seed.linkValue);
  const [removedIds, setRemovedIds] = useState([]);
  const [mediaMode, setMediaMode] = useState(seed.mediaMode);
  const [docMode, setDocMode] = useState(seed.docMode);
  const [linkMode, setLinkMode] = useState(seed.linkMode);
  const [pollMode, setPollMode] = useState(hadInitialPoll);
  const [pollData, setPollData] = useState(null);
  const photoInputRef = useRef(null);
  const videoInputRef = useRef(null);
  const documentInputRef = useRef(null);

  const photoCount = mediaItems.filter((item) => item.kind === "image").length;
  const videoCount = mediaItems.filter((item) => item.kind === "video").length;

  useEffect(() => {
    if (!onAttachmentsChange) return;

    onAttachmentsChange(
      buildAttachmentsPayload({
        mediaItems,
        docFile,
        removedIds,
        linkMode,
        linkValue,
        existingLinkId,
        initialLinkUrl,
        allowPoll,
        pollMode,
        pollData,
        hadInitialPoll,
      }),
    );
  }, [
    mediaItems,
    docFile,
    linkValue,
    linkMode,
    pollMode,
    pollData,
    removedIds,
    existingLinkId,
    initialLinkUrl,
    allowPoll,
    hadInitialPoll,
    onAttachmentsChange,
  ]);

  function handleSelect(type) {
    if (type.label === "Слика") {
      setMediaMode(true);
      photoInputRef.current?.click();
      return;
    }

    if (type.label === "Видео") {
      setMediaMode(true);
      videoInputRef.current?.click();
      return;
    }

    if (type.label === "Датотека") {
      setDocMode(true);
      documentInputRef.current?.click();
      return;
    }

    if (type.label === "Линк") {
      setLinkMode(true);
      return;
    }

    if (type.label === "Анкета") {
      setPollMode(true);
      return;
    }

    setSelected((prev) => (prev === type.label ? null : type.label));
  }

  function closeMedia() {
    const ids = mediaItems.map((item) => item.id).filter((id) => id != null);
    mediaItems.forEach(revokeItem);
    if (ids.length) {
      setRemovedIds((prev) => [...new Set([...prev, ...ids])]);
    }
    setMediaItems([]);
    setMediaMode(false);
  }

  function closeDocument() {
    if (existingDoc?.id != null) {
      setRemovedIds((prev) => appendUniqueId(prev, existingDoc.id));
    }
    setExistingDoc(null);
    setDocFile(null);
    setDocMode(false);
  }

  function closeLink() {
    if (existingLinkId != null) {
      setRemovedIds((prev) => appendUniqueId(prev, existingLinkId));
    }
    setExistingLinkId(null);
    setLinkValue("");
    setLinkMode(false);
  }

  function closePoll() {
    setPollData(null);
    setPollMode(false);
  }

  function removeMedia(url) {
    const target = mediaItems.find((item) => item.url === url);
    if (!target) return;

    revokeItem(target);
    if (target.id != null) {
      setRemovedIds((ids) => appendUniqueId(ids, target.id));
    }

    const next = mediaItems.filter((item) => item.url !== url);
    setMediaItems(next);
    if (next.length === 0) setMediaMode(false);
  }

  function addPhotos(fileList) {
    const incoming = Array.from(fileList || []).filter((file) => {
      if (file.size <= MAX_FILE_BYTES) return true;
      window.alert(`„${file.name}“ е преголем. Максимум е 100MB.`);
      return false;
    });

    setMediaItems((prev) => {
      const room = MAX_PHOTOS - prev.filter((item) => item.kind === "image").length;
      const added = incoming.slice(0, room).map((file) => ({
        url: URL.createObjectURL(file),
        file,
        kind: "image",
      }));
      return [...prev, ...added];
    });
    setMediaMode(true);
  }

  function addVideo(fileList) {
    const file = fileList?.[0];
    if (!file) return;

    if (file.size > MAX_FILE_BYTES) {
      window.alert(`„${file.name}“ е преголем. Максимум е 100MB.`);
      return;
    }

    setMediaItems((prev) => {
      if (prev.some((item) => item.kind === "video")) return prev;
      return [
        ...prev,
        {
          url: URL.createObjectURL(file),
          file,
          kind: "video",
        },
      ];
    });
    setMediaMode(true);
  }

  const hiddenLabels = [
    mediaMode && "Слика",
    mediaMode && "Видео",
    docMode && "Датотека",
    linkMode && "Линк",
    pollMode && "Анкета",
  ];
  const visibleTypes = TYPES.filter((type) => {
    if (!allowPoll && type.label === "Анкета") return false;
    return !hiddenLabels.includes(type.label);
  });

  const exclusiveDisabledMessage = "Прво избришете го тековниот прилог за да додадете нов.";

  function isTypeDisabled(label) {
    if (linkMode && (label === "Слика" || label === "Видео")) return true;
    if (mediaMode && label === "Линк") return true;
    if (docMode && label === "Анкета") return true;
    if (pollMode && label === "Датотека") return true;
    return false;
  }

  return (
    <div className={`flex max-w-full flex-col gap-3 ${widthClassName}`}>
      {mediaMode && (
        <MediaAttachments
          items={mediaItems}
          photoCount={photoCount}
          videoCount={videoCount}
          maxPhotos={MAX_PHOTOS}
          maxVideos={MAX_VIDEOS}
          onAddPhoto={() => photoInputRef.current?.click()}
          onAddVideo={() => videoInputRef.current?.click()}
          onRemove={removeMedia}
          onClose={closeMedia}
        />
      )}

      {docMode && (
        <DocumentAttachment
          file={docFile}
          fileName={existingDoc?.name}
          onAdd={() => documentInputRef.current?.click()}
          onClose={closeDocument}
        />
      )}

      {linkMode && <LinkAttachment value={linkValue} onChange={setLinkValue} onClose={closeLink} />}

      {allowPoll && pollMode && (
        <PollAttachment
          initialPoll={initialPoll}
          onClose={closePoll}
          onChange={setPollData}
        />
      )}

      <div className="flex gap-3">
        {visibleTypes.map((type) => {
          const disabled = isTypeDisabled(type.label);

          return (
            <PillButton
              key={type.label}
              label={type.label}
              selected={selected === type.label}
              onClick={() => handleSelect(type)}
              disabled={disabled}
              disabledMessage={disabled ? exclusiveDisabledMessage : undefined}
              leading={
                type.faIcon ? (
                  <FontAwesomeIcon icon={type.faIcon} className="h-4 w-4" />
                ) : (
                  type.icon && <img src={type.icon} alt="" className={`${type.iconClassName ?? "h-4"} w-auto`} />
                )
              }
              className="flex-1"
            />
          );
        })}
      </div>

      <input
        ref={photoInputRef}
        type="file"
        accept={PHOTO_ACCEPT}
        multiple
        className="hidden"
        onChange={(event) => {
          addPhotos(event.target.files);
          event.target.value = "";
        }}
      />
      <input
        ref={videoInputRef}
        type="file"
        accept={VIDEO_ACCEPT}
        className="hidden"
        onChange={(event) => {
          addVideo(event.target.files);
          event.target.value = "";
        }}
      />
      <input
        ref={documentInputRef}
        type="file"
        accept={DOCUMENT_ACCEPT}
        className="hidden"
        onChange={(event) => {
          const file = event.target.files?.[0] ?? null;
          if (file && file.size > MAX_FILE_BYTES) {
            window.alert(`„${file.name}“ е преголем. Максимум е 100MB.`);
            event.target.value = "";
            return;
          }
          if (existingDoc?.id != null) {
            setRemovedIds((prev) => appendUniqueId(prev, existingDoc.id));
          }
          setExistingDoc(null);
          setDocFile(file);
          setDocMode(true);
          event.target.value = "";
        }}
      />
    </div>
  );
}
