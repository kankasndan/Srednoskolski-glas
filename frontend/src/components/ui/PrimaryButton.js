// Violetovoto kopche od dizajn sistemot. Goleminata i disabled izgledot doagjaat
// od povikuvachot, bidejkji se razlikuvaat od mesto do mesto.
export default function PrimaryButton({ className = "", children, ...props }) {
  return (
    <button
      {...props}
      className={`cursor-pointer rounded-xl bg-[var(--color-primary-200)] font-bold text-white transition-colors hover:bg-[#4B25E0] disabled:cursor-not-allowed ${className}`}
    >
      {children}
    </button>
  );
}
