import Image from "next/image";

export default function ForumIcon({ src }) {
  return (
    <span className="relative size-4 shrink-0">
      <Image
        src={src}
        alt=""
        width={36}
        height={36}
        className="absolute left-1/2 top-1/2 size-9 max-w-none -translate-x-1/2 -translate-y-1/2"
      />
    </span>
  );
}
