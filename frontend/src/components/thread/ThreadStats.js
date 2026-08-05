import Image from "next/image";

function Stat({ icon, label, count }) {
  return (
    <div className="flex h-10 w-24 items-center justify-center gap-4 rounded-xl border border-[#CCCCCC] opacity-80">
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span className="text-[14px] leading-none text-black">
        <span className="sr-only">{label}: </span>
        {count}
      </span>
    </div>
  );
}

export default function ThreadStats({ views, comments, votes }) {
  return (
    <div className="flex gap-2">
      <Stat icon="/eye-line.svg" label="Прегледи" count={views} />
      <Stat icon="/chat-1-line.svg" label="Коментари" count={comments} />
      <Stat icon="/Chevrons up.svg" label="Гласови" count={votes} />
    </div>
  );
}
