export default function FieldLabel({ children, required = false, htmlFor }) {
  const Tag = htmlFor ? "label" : "span";

  return (
    <Tag
      htmlFor={htmlFor}
      className="font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black"
    >
      {required && <span className="text-red-500">*</span>}
      {children}
    </Tag>
  );
}
