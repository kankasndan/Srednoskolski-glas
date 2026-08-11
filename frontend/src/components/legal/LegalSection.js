export default function LegalSection({ title, children }) {
  return (
    <section className="flex flex-col gap-3">
      <h2 className="text-[20px] font-bold text-[var(--color-grays-900)]">
        {title}
      </h2>
      <div className="flex max-w-4xl flex-col gap-4 text-[16px] text-[var(--color-grays-800)]">
        {children}
      </div>
    </section>
  );
}
