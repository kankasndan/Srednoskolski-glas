export default function FieldLabel({ children, required = false, htmlFor, className = "mb-2" }) {
  const Tag = htmlFor ? "label" : "span";

  return (
    <Tag
      htmlFor={htmlFor}
      className={`font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black ${className}`}
    >
      {required && <span className="text-red-500">*</span>}
      {children}
    </Tag>
  );
}
