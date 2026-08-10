import Image from "next/image";

const FALLBACK_ICON = "/icons/opshti_diskusii.svg";

export default function ForumIcon({
  src,
  active = false,
  className,
  imageClassName = "size-9 max-w-none",
  wrapperClassName = "size-4",
}) {
  const iconSrc = src || FALLBACK_ICON;
  const isRemote = /^https?:\/\//i.test(iconSrc);
  const wrapper = className ?? wrapperClassName;

  return (
    <span className={`relative shrink-0 ${wrapper}`}>
      {isRemote ? (
        <img
          src={iconSrc}
          alt=""
          className={`absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 ${imageClassName} ${
            active ? "brightness-0 invert" : ""
          }`}
        />
      ) : (
        <Image
          src={iconSrc}
          alt=""
          width={36}
          height={36}
          className={`absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 ${imageClassName} ${
            active ? "brightness-0 invert" : ""
          }`}
        />
      )}
    </span>
  );
}
