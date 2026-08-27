export default function AboutSectionTitle({ children }) {
  return (
    <h2 className="text-[clamp(24px,3.125vw,32px)] font-bold leading-[1.33] text-[var(--color-primary-200)]">
      {children}
    </h2>
  );
}
