export default function MailLink({ address }) {
  return (
    <a
      href={`mailto:${address}`}
      className="font-bold text-[var(--color-primary-200)] underline-offset-2 hover:underline"
    >
      {address}
    </a>
  );
}
