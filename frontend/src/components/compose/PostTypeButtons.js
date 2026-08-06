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
  URL.revokeObjectURL(item.url);
}

export default function PostTypeButtons({ widthClassName = "w-[632px]", onAttachmentsChange }) {
  const [selected, setSelected] = useState(null);
  /** Ordered mix of { url, file, kind: "image" | "video" } — order is submitted as-is. */
  const [mediaItems, setMediaItems] = useState([]);
  const [docFile, setDocFile] = useState(null);
  const [linkValue, setLinkValue] = useState("");
  const [mediaMode, setMediaMode] = useState(false);
  const [docMode, setDocMode] = useState(false);
  const [linkMode, setLinkMode] = useState(false);
  const [pollMode, setPollMode] = useState(false);
  const [pollData, setPollData] = useState(null);
  const photoInputRef = useRef(null);
  const videoInputRef = useRef(null);
  const documentInputRef = useRef(null);

  const photoCount = mediaItems.filter((item) => item.kind === "image").length;
  const videoCount = mediaItems.filter((item) => item.kind === "video").length;

  useEffect(() => {
    if (!onAttachmentsChange) return;

    const files = [
      ...mediaItems.map((item) => item.file).filter(Boolean),
      ...(docFile ? [docFile] : []),
    ];

    onAttachmentsChange({
      files,
      link: linkMode ? linkValue.trim() : "",
      poll:
        pollMode &&
        pollData?.question &&
        pollData.options?.length >= 2 &&
        pollData.duration_days
          ? pollData
          : null,
    });
  }, [mediaItems, docFile, linkValue, linkMode, pollMode, pollData, onAttachmentsChange]);

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
    mediaItems.forEach(revokeItem);
    setMediaItems([]);
    setMediaMode(false);
  }

  function closeDocument() {
    setDocFile(null);
    setDocMode(false);
  }

  function closeLink() {
    setLinkValue("");
    setLinkMode(false);
  }

  function closePoll() {
    setPollData(null);
    setPollMode(false);
  }

  function removeMedia(url) {
    setMediaItems((prev) => {
      const target = prev.find((item) => item.url === url);
      if (target) revokeItem(target);
      const next = prev.filter((item) => item.url !== url);
      if (next.length === 0) setMediaMode(false);
      return next;
    });
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
  const visibleTypes = TYPES.filter((type) => !hiddenLabels.includes(type.label));

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
          onAdd={() => documentInputRef.current?.click()}
          onClose={closeDocument}
        />
      )}

      {linkMode && <LinkAttachment value={linkValue} onChange={setLinkValue} onClose={closeLink} />}

      {pollMode && (
        <PollAttachment
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
          setDocFile(file);
          event.target.value = "";
        }}
      />
    </div>
  );
}
