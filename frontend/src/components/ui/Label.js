// Sivoto livche od dizajn sistemot ("label-normal").
export default function Label({ children }) {
  return (
    <span className="flex h-6 items-center justify-center rounded-md border-[0.5px] border-[var(--color-grays-300)] bg-[var(--color-grays-200)] px-2 py-1 text-[12px] text-[var(--color-grays-900)]">
      {children}
    </span>
  );
}
