"use client";

import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";

export default function AvatarUploadCard() {
  const router = useRouter();
  const fileInputRef = useRef(null);
  const [previewUrl, setPreviewUrl] = useState(null);
  const [isDragging, setIsDragging] = useState(false);

  useEffect(() => {
    return () => {
      if (previewUrl) URL.revokeObjectURL(previewUrl);
    };
  }, [previewUrl]);

  function openFilePicker() {
    fileInputRef.current?.click();
  }

  function showPreview(file) {
    if (!file || !file.type.startsWith("image/")) return;
    setPreviewUrl(URL.createObjectURL(file));
  }

  function handleFileChange(e) {
    showPreview(e.target.files[0]);
  }

  function handleDrop(e) {
    e.preventDefault();
    setIsDragging(false);
    showPreview(e.dataTransfer.files[0]);
  }

  return (
    <div
      className="mx-auto flex h-[518px] w-[850px] max-w-full flex-col rounded-2xl bg-[#E5E5E5] pt-10 pr-20 pb-5 pl-20"
      style={{ boxShadow: "7px 7px 9.4px 0px #00000026" }}
    >
      <input
        ref={fileInputRef}
        type="file"
        accept="image/*"
        onChange={handleFileChange}
        className="hidden"
      />

      <button
        type="button"
        onClick={openFilePicker}
        onDragOver={(e) => {
          e.preventDefault();
          setIsDragging(true);
        }}
        onDragLeave={() => setIsDragging(false)}
        onDrop={handleDrop}
        className={`flex w-full flex-1 flex-col items-center justify-center gap-5 rounded-2xl border-2 border-dashed px-6 py-10 transition-colors ${
          isDragging ? "border-[#582FF5] bg-[#582FF5]/5" : "border-[#B5B5B5]"
        }`}
      >
        {previewUrl ? (
          <span className="flex size-[166px] items-center justify-center overflow-hidden rounded-full">
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
            className="size-[166px]"
            priority
          />
        )}

        <p className="text-center font-(family-name:--font-manrope) text-[20px] font-normal leading-[22.59px] text-[#333333]">
          Прикачи своја фотографија,
          <br />а ние ќе ја претвориме во{" "}
          <span className="text-[#582FF5]">твој личен аватар.</span>
        </p>
      </button>

      <button
        type="button"
        onClick={previewUrl ? () => router.push("/feed") : openFilePicker}
        className="mx-auto mt-8 h-14 w-[400px] max-w-full rounded-2xl bg-[#582FF5] font-(family-name:--font-manrope) text-[15px] font-bold text-white transition-colors hover:bg-[#4B25E0]"
      >
        {previewUrl ? "Продолжи" : "Прикачи фотографија"}
      </button>

      <button
        type="button"
        onClick={() => router.push("/feed")}
        className="mx-auto mt-6 block text-center font-(family-name:--font-manrope) text-[16px] font-normal leading-none text-[#737373] transition-colors hover:text-[#333333]"
      >
        Можеби подоцна
      </button>
    </div>
  );
}
