import AboutCta from "@/components/about/AboutCta";
import AboutHero from "@/components/about/AboutHero";
import AboutSectionTitle from "@/components/about/AboutSectionTitle";
import AppShell from "@/components/shell/AppShell";
import BackButton from "@/components/shell/BackButton";
import Image from "next/image";
import Label from "@/components/ui/Label";
import MobileFooter from "@/components/shell/MobileFooter";
import TeamMember from "@/components/about/TeamMember";
import TeamQuote from "@/components/about/TeamQuote";
import { TEAM_MEMBERS } from "@/lib/teamMembers";

const TEAM_STATS = ["10 членови", "4 различни улоги", "1 проект"];

// Ne e vrzana vo navigacijata - dostapna samo preku direkten link /about.
export default function AboutPage() {
  return (
    <AppShell>
      <div className="flex w-[1216px] max-w-full flex-col pb-2 font-(family-name:--font-manrope) leading-[normal] lg:pb-12">
        <div className="flex flex-col gap-[32px] lg:gap-16">
          <div className="self-start">
            <BackButton label="Назад" tone="muted" />
          </div>
          <AboutHero />
        </div>

        <section className="mt-10 flex w-full max-w-80 flex-col items-center gap-8 self-center md:max-w-[418px] lg:mt-30 lg:max-w-none">
          <AboutSectionTitle>Зошто го создадовме?</AboutSectionTitle>
          <div className="flex max-w-full flex-col gap-5 text-center text-[clamp(16px,1.75vw,18px)] leading-[1.5] text-[var(--color-grays-700)] lg:max-w-[418px]">
            <p>
              Секој средношколец има глас. Но честопати нема место каде тој глас
              навистина се слуша.
            </p>
            <p>
              Сакавме да создадеме простор каде учениците слободно прашуваат,
              споделуваат и комуницираат со луѓе кои ги разбираат нивните
              искуства - бидејќи и самите ние сме поминале низ истото.
            </p>
          </div>
        </section>

        <section className="mt-18 flex flex-col gap-6 lg:mt-36 lg:gap-10">
          <div className="flex flex-col items-center gap-6 text-center lg:flex-row lg:flex-wrap lg:justify-between lg:gap-4 lg:text-left">
            <AboutSectionTitle>Ова сме ние!</AboutSectionTitle>
            <div className="flex flex-wrap items-center justify-center gap-4">
              {TEAM_STATS.map((stat) => (
                <Label key={stat}>{stat}</Label>
              ))}
            </div>
          </div>

          <div className="flex flex-col gap-16">
            <div className="overflow-hidden rounded-3xl lg:h-auto lg:min-h-[480px]">
              <Image
                src="/about/grupna.png"
                alt="Групна слика од фотосесијата"
                width={1600}
                height={1067}
                className="h-auto w-full rounded-3xl object-cover object-center lg:size-full"
              />
            </div>

            <ul className="flex h-[180px] w-full max-w-[1101px] gap-4 overflow-x-auto overscroll-x-contain py-px [scrollbar-width:none] lg:grid lg:h-auto lg:grid-cols-[repeat(auto-fit,minmax(201px,1fr))] lg:gap-6 lg:self-center lg:overflow-visible lg:overscroll-auto lg:py-0 [&::-webkit-scrollbar]:hidden">
              {TEAM_MEMBERS.map((member) => (
                <TeamMember key={member.name} member={member} />
              ))}
            </ul>
          </div>
        </section>

        <TeamQuote className="mt-[46px] lg:mt-[93px]" />
        <AboutCta className="mt-15 lg:mt-30" />
        <MobileFooter className="mt-24" />
      </div>
    </AppShell>
  );
}
