"use client";

import Image from "next/image";
import { useState } from "react";

// Prikazuva profilna slika. Sekoj korisnik dobiva slika od backendot (AI ili
// nekoja od podednachenite), pa `src` obichno postoi — fallbackot samo pokriva
// slika shto nema da se vchita.
//
// Upotreba:
//   <Avatar src={user.imageUrl} alt="" />           // md (32px) po default
//   <Avatar src={user.imageUrl} size="sm" alt="" /> // 24px
//   <Avatar src={user.imageUrl} size="lg" alt="" /> // 40px
//   <Avatar src={user.imageUrl} size="xl" alt="" /> // 56px

const FALLBACK = "/Generic avatar.svg";

const SIZES = {
  sm: { px: 24, className: "size-6" },
  md: { px: 32, className: "size-8" },
  lg: { px: 40, className: "size-10" },
  xl: { px: 56, className: "size-14" },
};

export default function Avatar({ src, size = "md", alt = "" }) {
  const [failed, setFailed] = useState(false);
  const { px, className } = SIZES[size];

  return (
    <Image
      src={failed || !src ? FALLBACK : src}
      alt={alt}
      width={px}
      height={px}
      onError={() => setFailed(true)}
      className={`${className} shrink-0 rounded-full object-cover`}
    />
  );
}
