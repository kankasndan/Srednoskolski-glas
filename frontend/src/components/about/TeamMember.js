import Avatar from "@/components/ui/Avatar";

export default function TeamMember({ member }) {
  return (
    <li className="flex flex-col items-center justify-center gap-4 rounded-2xl border border-[var(--color-secondary-200)] p-6">
      <div className="flex size-24 shrink-0 overflow-hidden rounded-full border border-[var(--color-grays-900)]">
        <Avatar src={member.photo} size="3xl" sizeClassName="size-24" alt="" />
      </div>
      <div className="flex flex-col items-center gap-1 text-center text-[16px] lg:whitespace-nowrap">
        <p className="font-bold text-[var(--color-primary-200)]">
          {member.role}
        </p>
        <p className="text-[var(--color-grays-900)]">{member.name}</p>
      </div>
    </li>
  );
}
