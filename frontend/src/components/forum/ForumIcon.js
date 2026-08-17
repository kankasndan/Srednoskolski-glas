import Image from "next/image";

const FALLBACK_ICON = "/icons/opshti_diskusii.svg";

// Ikonite vo /public/icons se normalizirani: crtezot zafaka 82% od kvadratno
// platno i e centriran. Zatoa ovde nema zumiranje — ikonata samo se vklopuva
// vo kutijata i site forumi izleguvaat ista golemina.
//
// Kutija od 20px (size-5) dava crtez od ~16px, isto kako tagovite na feedot.
export default function ForumIcon({
  src,
  active = false,
  className,
  imageClassName = "",
  wrapperClassName = "size-5",
}) {
  const iconSrc = src || FALLBACK_ICON;
  const isRemote = /^https?:\/\//i.test(iconSrc);
  const wrapper = className ?? wrapperClassName;
  const image = `size-full object-contain ${imageClassName} ${
    active ? "brightness-0 invert" : ""
  }`;

  return (
    <span className={`block shrink-0 ${wrapper}`}>
      {isRemote ? (
        <img src={iconSrc} alt="" className={image} />
      ) : (
        <Image src={iconSrc} alt="" width={40} height={40} className={image} />
      )}
    </span>
  );
}
