"use client";

import { useRef, useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faLink } from "@fortawesome/free-solid-svg-icons";
import DocumentAttachment from "@/components/DocumentAttachment";
import LinkAttachment from "@/components/LinkAttachment";
import MediaAttachments from "@/components/MediaAttachments";
import PillButton from "@/components/PillButton";
import PollAttachment from "@/components/PollAttachment";

config.autoAddCss = false;

const MAX_PHOTOS = 10;
const MAX_VIDEOS = 1;
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

function addFiles(setList, files, max) {
  const incoming = Array.from(files);
  setList((prev) => {
    const room = max - prev.length;
    const added = incoming
      .slice(0, room)
      .map((file) => ({ url: URL.createObjectURL(file), file }));
    return [...prev, ...added];
  });
}

function removeFile(setList, url) {
  URL.revokeObjectURL(url);
  setList((prev) => prev.filter((item) => item.url !== url));
}

function clearFiles(list, setList) {
  list.forEach((item) => URL.revokeObjectURL(item.url));
  setList([]);
}

export default function PostTypeButtons() {
  const [selected, setSelected] = useState(null);
  const [photos, setPhotos] = useState([]);
  const [videos, setVideos] = useState([]);
  const [docFile, setDocFile] = useState(null);
  const [linkValue, setLinkValue] = useState("");
  const [photoMode, setPhotoMode] = useState(false);
  const [videoMode, setVideoMode] = useState(false);
  const [docMode, setDocMode] = useState(false);
  const [linkMode, setLinkMode] = useState(false);
  const [pollMode, setPollMode] = useState(false);
  const photoInputRef = useRef(null);
  const videoInputRef = useRef(null);
  const documentInputRef = useRef(null);

  function handleSelect(type) {
    if (type.label === "Слика") {
      setPhotoMode(true);
      photoInputRef.current.click();
      return;
    }

    if (type.label === "Видео") {
      setVideoMode(true);
      videoInputRef.current.click();
      return;
    }

    if (type.label === "Датотека") {
      setDocMode(true);
      documentInputRef.current.click();
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

  function closePhotos() {
    clearFiles(photos, setPhotos);
    setPhotoMode(false);
  }

  function closeVideos() {
    clearFiles(videos, setVideos);
    setVideoMode(false);
  }

  function closeDocument() {
    setDocFile(null);
    setDocMode(false);
  }

  function closeLink() {
    setLinkValue("");
    setLinkMode(false);
  }

  const hiddenLabels = [
    photoMode && "Слика",
    videoMode && "Видео",
    docMode && "Датотека",
    linkMode && "Линк",
    pollMode && "Анкета",
  ];
  const visibleTypes = TYPES.filter((type) => !hiddenLabels.includes(type.label));

  const exclusiveDisabledMessage = "Прво избришете го тековниот прилог за да додадете нов.";
  const EXCLUSIVE_GROUPS = [
    { labels: ["Слика", "Видео", "Линк"], active: photoMode ? "Слика" : videoMode ? "Видео" : linkMode ? "Линк" : null },
    { labels: ["Датотека", "Анкета"], active: docMode ? "Датотека" : pollMode ? "Анкета" : null },
  ];

  return (
    <div className="flex w-[632px] max-w-full flex-col gap-3">
      {photoMode && (
        <MediaAttachments
          kind="image"
          items={photos}
          max={MAX_PHOTOS}
          onAdd={() => photoInputRef.current.click()}
          onRemove={(url) => removeFile(setPhotos, url)}
          onClose={closePhotos}
        />
      )}

      {videoMode && (
        <MediaAttachments
          kind="video"
          items={videos}
          max={MAX_VIDEOS}
          onAdd={() => videoInputRef.current.click()}
          onRemove={(url) => removeFile(setVideos, url)}
          onClose={closeVideos}
        />
      )}

      {docMode && (
        <DocumentAttachment
          file={docFile}
          onAdd={() => documentInputRef.current.click()}
          onClose={closeDocument}
        />
      )}

      {linkMode && <LinkAttachment value={linkValue} onChange={setLinkValue} onClose={closeLink} />}

      {pollMode && <PollAttachment onClose={() => setPollMode(false)} />}

      <div className="flex gap-3">
        {visibleTypes.map((type) => {
          const disabled = EXCLUSIVE_GROUPS.some(
            (group) => group.active !== null && group.active !== type.label && group.labels.includes(type.label)
          );

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
          addFiles(setPhotos, event.target.files, MAX_PHOTOS);
          event.target.value = "";
        }}
      />
      <input
        ref={videoInputRef}
        type="file"
        accept={VIDEO_ACCEPT}
        className="hidden"
        onChange={(event) => {
          addFiles(setVideos, event.target.files, MAX_VIDEOS);
          event.target.value = "";
        }}
      />
      {/* TODO prikachi fajl koga kje ima endpoint */}
      <input
        ref={documentInputRef}
        type="file"
        accept={DOCUMENT_ACCEPT}
        className="hidden"
        onChange={(event) => {
          setDocFile(event.target.files[0] ?? null);
          event.target.value = "";
        }}
      />
    </div>
  );
}
