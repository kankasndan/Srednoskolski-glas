// Komentarite od prvo nivo se sivi, odgovorite se crni.
export default function CommentBody({ text, muted }) {
  return (
    <p
      className={`text-[14px] leading-[22px] ${
        muted ? "text-[#595959]" : "text-black"
      }`}
    >
      {text.split(/(@\S+)/).map((part, index) =>
        part.startsWith("@") ? (
          <span key={index} className="text-[#582FF5]">
            {part}
          </span>
        ) : (
          part
        )
      )}
    </p>
  );
}
