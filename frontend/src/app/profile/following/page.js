import ProfileFollowedForums from "@/components/profile/ProfileFollowedForums";
import ProfileFollowedThreads from "@/components/profile/ProfileFollowedThreads";

function Section({ title, children }) {
  return (
    <section className="flex flex-col gap-4 md:gap-4">
      <h2 className="font-(family-name:--font-manrope) text-[16px] font-bold text-black md:text-[18px]">
        {title}
      </h2>
      {children}
    </section>
  );
}

export default function ProfileFollowingPage() {
  return (
    <div className="flex flex-col gap-14 md:gap-8">
      <Section title="Форуми што ги следиш">
        <ProfileFollowedForums />
      </Section>
      <Section title="Дискусии што ги следиш">
        <ProfileFollowedThreads />
      </Section>
    </div>
  );
}
