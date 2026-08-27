import Avatar from "@/components/ui/Avatar";

export default function TeamMember({ member }) {
  const roleLines = member.role.split("\n");

  return (
    <li className="flex h-[178px] w-[201px] shrink-0 flex-col items-center justify-start gap-4 rounded-2xl border border-[var(--color-secondary-200)] p-6 lg:h-auto lg:w-full lg:shrink lg:p-6">
      <div className="flex size-16 shrink-0 overflow-hidden rounded-full border border-[var(--color-grays-900)] lg:size-24">
        <Avatar
          src={member.photo}
          size="3xl"
          sizeClassName="size-16 lg:size-24"
          alt=""
        />
      </div>
      <div className="flex min-h-12 w-full flex-col items-center gap-1 text-center text-[16px] leading-[1.28] lg:h-auto lg:w-auto lg:whitespace-nowrap">
        <p className="font-bold text-[var(--color-primary-200)] lg:max-w-none">
          {roleLines.map((line) => (
            <span key={line} className="block whitespace-nowrap">
              {line}
            </span>
          ))}
        </p>
        <p className="max-w-full font-normal text-[var(--color-grays-900)] lg:max-w-none lg:whitespace-nowrap">
          {member.name}
        </p>
      </div>
    </li>
  );
}
