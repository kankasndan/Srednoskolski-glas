"use client";

import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { generateOnboardingAvatar } from "@/api/onboarding";
import { userFacingError } from "@/lib/api";
import { loadSessionUser } from "@/lib/sessionUser";

function finishOnboarding(router) {
  localStorage.removeItem("onboarding_pending");
  // Ensure /api/me capabilities (create threads, etc.) are fresh on the feed.
  loadSessionUser({ force: true }).finally(() => router.push("/feed"));
}

export default function AvatarUploadCard() {
  const router = useRouter();
  const fileInputRef = useRef(null);
  const [file, setFile] = useState(null);
  const [previewUrl, setPreviewUrl] = useState(null);
  const [isDragging, setIsDragging] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    return () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
    };
  }, [previewUrl]);

  function openFilePicker() {
    if (submitting) return;
    fileInputRef.current?.click();
  }

  function showPreview(nextFile) {
    if (!nextFile || !nextFile.type.startsWith("image/")) return;

    setError("");
    setFile(nextFile);
    setPreviewUrl((current) => {
      if (current) URL.revokeObjectURL(current);
      return URL.createObjectURL(nextFile);
    });
  }

  function handleFileChange(e) {
    showPreview(e.target.files[0]);
  }

  function handleDrop(e) {
    e.preventDefault();
    setIsDragging(false);
    showPreview(e.dataTransfer.files[0]);
  }

  async function handleContinue() {
    if (!file) {
      openFilePicker();
      return;
    }

    setSubmitting(true);
    setError("");

    try {
      await generateOnboardingAvatar(file);
      finishOnboarding(router);
    } catch (err) {
      setError(
        userFacingError(
          err,
          err.status >= 500 || err.status === 0
            ? "Создавањето траеше предолго. Обиди се повторно."
            : "Не успеавме да го создадеме аватарот. Обиди се повторно.",
        ),
      );
      setSubmitting(false);
    }
  }

  return (
    <div
      className="flex w-full max-w-[342px] flex-col items-center gap-14 sm:max-w-[clamp(342px,67.4vw,690px)] lg:max-w-[850px] lg:gap-8 lg:rounded-2xl lg:bg-[#E5E5E5] lg:px-20 lg:pt-10 lg:pb-5 lg:shadow-[7px_7px_4.7px_0px_rgba(0,0,0,0.15)]"
    >
      <input
        ref={fileInputRef}
        type="file"
        accept="image/jpeg,image/png,image/webp"
        onChange={handleFileChange}
        className="hidden"
      />

      <button
        type="button"
        onClick={openFilePicker}
        disabled={submitting}
        onDragOver={(e) => {
          e.preventDefault();
          if (!submitting) setIsDragging(true);
        }}
        onDragLeave={() => setIsDragging(false)}
        onDrop={handleDrop}
        className={`flex h-[182px] w-full cursor-pointer rounded-2xl p-3 shadow-[7px_7px_9.4px_0px_rgba(0,0,0,0.15)] transition-colors sm:h-[clamp(182px,31.6vw,324px)] sm:w-[clamp(342px,67.4vw,690px)] lg:h-auto lg:bg-transparent lg:p-0 lg:shadow-none disabled:cursor-wait ${
          isDragging
            ? "bg-[#582FF5]/5"
            : "bg-[#E5E5E5] lg:bg-transparent"
        }`}
      >
        <div className="relative flex h-full w-full flex-col items-center justify-center gap-4 rounded-2xl p-3 sm:gap-[clamp(16px,3.1vw,32px)] sm:p-[clamp(12px,3.9vw,40px)] lg:h-[324px] lg:w-[690px] lg:gap-8 lg:p-10">
          <svg
            aria-hidden="true"
            className="pointer-events-none absolute inset-0 size-full"
            preserveAspectRatio="none"
            viewBox="0 0 100 100"
          >
            <rect
              x="0.5"
              y="0.5"
              width="99"
              height="99"
              rx="4.8"
              fill="none"
              stroke={isDragging ? "#582FF5" : "#000000"}
              strokeWidth="1"
              strokeDasharray="12 12"
              vectorEffect="non-scaling-stroke"
            />
          </svg>

          {previewUrl ? (
            <span className="flex size-[92px] items-center justify-center overflow-hidden rounded-full sm:size-[clamp(92px,16.2vw,166px)] lg:size-[166px]">
              <img
                src={previewUrl}
                alt="Преглед на фотографијата"
                className="size-full object-cover"
              />
            </span>
          ) : (
            <Image
              src="/Generic_avatar_onboarding.svg"
              alt=""
              width={166}
              height={166}
              className="size-[92px] sm:size-[clamp(92px,16.2vw,166px)] lg:size-[166px]"
              priority
            />
          )}

          <p className="max-w-[280px] text-center font-(family-name:--font-manrope) text-[14px] font-normal leading-none text-black sm:max-w-[clamp(280px,49vw,502px)] sm:text-[clamp(14px,1.95vw,20px)] sm:leading-[1.13] lg:max-w-[502px] lg:text-[20px] lg:leading-[22.595px]">
            Прикачи своја фотографија. Ќе направиме стилизиран аватар за твојот
            профил — оригиналната слика не се зачувува.
          </p>
        </div>
      </button>

      <div className="flex w-full flex-col items-center gap-[21px] lg:gap-6">
        {error ? (
          <p className="max-w-[400px] text-center font-(family-name:--font-manrope) text-[13px] text-[#DC2626]">
            {error}
          </p>
        ) : null}

        <button
          type="button"
          onClick={handleContinue}
          disabled={submitting}
          className="h-10 w-full cursor-pointer rounded-2xl bg-[#582FF5] font-(family-name:--font-manrope) text-[16px] font-bold text-white transition-colors hover:bg-[#3300F5] disabled:cursor-wait disabled:opacity-70 lg:h-14 lg:max-w-[400px]"
        >
          {submitting
            ? "Се создава аватарот…"
            : previewUrl
              ? "Продолжи"
              : "Прикачи фотографија"}
        </button>

        <button
          type="button"
          onClick={() => finishOnboarding(router)}
          disabled={submitting}
          className="cursor-pointer text-center font-(family-name:--font-manrope) text-[14px] font-normal leading-none text-[#595959] transition-colors hover:text-[#333333] disabled:cursor-wait disabled:opacity-50 lg:text-[16px]"
        >
          Можеби подоцна
        </button>
      </div>
    </div>
  );
}
