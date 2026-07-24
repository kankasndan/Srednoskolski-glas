import Image from "next/image";

function ActionButton({ icon, iconClassName = "", label, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="flex cursor-pointer items-center gap-1.5 text-[12px] leading-none text-[#595959] transition-colors hover:text-black"
    >
      <Image
        src={icon}
        alt=""
        width={16}
        height={16}
        className={`size-4 ${iconClassName}`}
      />
      {label}
    </button>
  );
}

export default function CommentActions({ votes, hasReplies, collapsed, onToggle, onReply }) {
  return (
    <div className="flex items-center gap-4">
      {/* TODO glasanje koga kje ima endpoint */}
      <button
        type="button"
        className="flex cursor-pointer items-center gap-2 rounded-lg border border-[#CCCCCC] px-4 py-2 text-[12px] leading-none text-black opacity-80"
      >
        <Image
          src="/Chevrons up.svg"
          alt=""
          width={16}
          height={16}
          className="size-4"
        />
        {votes}
      </button>

      {/* TODO prijavi i spodeli koga kje ima endpoint */}
      <ActionButton
        icon="/comments icon/comment.svg"
        label="Одговори"
        onClick={onReply}
      />
      <ActionButton icon="/comments icon/report.svg" label="Пријави" />
      <ActionButton icon="/comments icon/share.svg" label="Сподели" />

      {hasReplies ? (
        <ActionButton
          icon="/comments icon/show less arroew.svg"
          iconClassName={collapsed ? "rotate-180" : ""}
          label={collapsed ? "Прикажи повеќе" : "Прикажи помалку"}
          onClick={onToggle}
        />
      ) : null}
    </div>
  );
}
