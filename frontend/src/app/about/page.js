import AboutCta from "@/components/about/AboutCta";
import AboutHero from "@/components/about/AboutHero";
import AboutSectionTitle from "@/components/about/AboutSectionTitle";
import AppShell from "@/components/shell/AppShell";
import BackButton from "@/components/shell/BackButton";
import Label from "@/components/ui/Label";
import TeamMember from "@/components/about/TeamMember";
import TeamQuote from "@/components/about/TeamQuote";
import { TEAM_MEMBERS } from "@/lib/teamMembers";

const TEAM_STATS = ["10 членови", "4 различни улоги", "1 проект"];

// Ne e vrzana vo navigacijata - dostapna samo preku direkten link /about.
export default function AboutPage() {
  return (
    <AppShell>
      <div className="flex w-[1216px] max-w-full flex-col pb-12 font-(family-name:--font-manrope) leading-[normal]">
        <div className="flex flex-col gap-16">
          <div className="self-start">
            <BackButton label="Назад" tone="muted" />
          </div>
          <AboutHero />
        </div>

        <section className="mt-30 flex flex-col items-center gap-8">
          <AboutSectionTitle>Зошто го создадовме?</AboutSectionTitle>
          <div className="flex max-w-[418px] flex-col gap-5 text-center text-[16px] text-[var(--color-grays-700)]">
            <p>
              Секој средношколец има глас. Но честопати нема место каде тој глас
              навистина се слуша.
            </p>
            <p>
              Сакавме да создадеме простор каде учениците слободно прашуваат,
              споделуваат и комуницираат со луѓе кои ги разбираат нивните
              искуства - бидејќи самите и ние сме поминале низ истото.
            </p>
          </div>
        </section>

        <section className="mt-36 flex flex-col gap-10">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <AboutSectionTitle>Ова сме ние!</AboutSectionTitle>
            <div className="flex flex-wrap items-center gap-4">
              {TEAM_STATS.map((stat) => (
                <Label key={stat}>{stat}</Label>
              ))}
            </div>
          </div>

          <div className="flex flex-col gap-16">
            <div className="flex min-h-[240px] items-center justify-center rounded-3xl bg-[url('/thread-placeholder.png')] bg-cover bg-center p-6 lg:min-h-[480px]">
              <p className="text-[20px] font-bold text-[var(--color-grays-900)]">
                Групна слика од фотосесијата
              </p>
            </div>

            <ul className="grid w-full max-w-[1101px] grid-cols-2 gap-6 self-center sm:grid-cols-3 lg:grid-cols-5">
              {TEAM_MEMBERS.map((member) => (
                <TeamMember key={member.name} member={member} />
              ))}
            </ul>
          </div>
        </section>

        <TeamQuote className="mt-[93px]" />
        <AboutCta className="mt-30" />
      </div>
    </AppShell>
  );
}
