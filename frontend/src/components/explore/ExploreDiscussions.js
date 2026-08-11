import Image from "next/image";
import AppShell from "@/components/shell/AppShell";
import Threads from "@/components/thread/Threads";
import exploreDiscussions from "../../../public/explore-discussions-mock.json";

const featuredForums = [
  {
    slug: "opshti_diskusii",
    name: "Општи дискусии",
    icon: "/icons/opshti_diskusii.svg",
    members: 120,
    description: "Разговори за сè што е дел од средношколскиот живот.",
  },
  {
    slug: "tehnologija_i_programiranje",
    name: "Технологија и програмирање",
    icon: "/icons/tehnologija_i_programiranje.svg",
    members: 426,
    description: "Кодирање, проекти, технологии и корисни дигитални алатки.",
  },
  {
    slug: "mentalno_zdravje",
    name: "Ментално здравје",
    icon: "/icons/mentalno_zdravje.svg",
    members: 52,
    description: "Простор за разговор за стрес, балансот и менталното здравје.",
  },
  {
    slug: "pomosh_pri_uchenje",
    name: "Помош при учење",
    icon: "/icons/pomosh_pri_uchenje.svg",
    members: 119,
    description: "Прашања, објаснувања и совети за полесно и подобро учење.",
  },
];

export default function ExploreDiscussions() {
  const threads = Array.isArray(exploreDiscussions.data) ? exploreDiscussions.data : [];

  return (
    <AppShell>
      <div className="flex w-[992px] max-w-full flex-col gap-16">
        <div className="flex flex-col">
          <h1 className="font-[family-name:var(--font-manrope)] text-[24px] font-bold tracking-normal text-[#582FF5]">
            Истражи
          </h1>
          <p className="max-w-[720px] font-[family-name:var(--font-manrope)] text-[16px] tracking-normal text-[#595959]">
            Откриј популарни заедници и дискусии што ги движат средношколците низ целата земја.
          </p>
        </div>

        <ExploreSection title="Најпосетувани форуми">
          <div className="grid gap-6 md:grid-cols-2">
            {featuredForums.map((forum) => (
              <FeaturedForumCard key={forum.slug} forum={forum} />
            ))}
          </div>
        </ExploreSection>

        <ExploreSection title="Најпопуларни дискусии оваа недела">
            <div className="explore-hide-filters">
              <Threads defaultSort="top" staticThreads={threads} />
            </div>
        </ExploreSection>
      </div>
    </AppShell>
  );
}

function ExploreSection({ title, children }) {
  return (
    <section className="flex flex-col gap-6">
      <h2 className="font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-black">
        {title}
      </h2>
      {children}
    </section>
  );
}

function FeaturedForumCard({ forum }) {
  return (
    <article className="flex min-h-[141px] flex-col justify-between rounded-3xl border border-[#CFE9ED] bg-white p-6">
      <div className="flex items-start justify-between gap-4">
        <div className="flex min-w-0 items-center gap-4">
          <div className="flex size-14 shrink-0 items-center justify-center rounded-xl bg-[#DCEBED]">
            <Image
              src={forum.icon}
              alt=""
              width={32}
              height={32}
              className="size-[32px] object-contain"
            />
          </div>

          <div className="min-w-0">
            <h3 className="truncate text-[14px] font-extrabold uppercase leading-[19px] text-black font-[family-name:var(--font-oswald)]">
              {forum.name}
            </h3>
            <p className="mt-1 flex items-center gap-1 text-[12px] leading-none text-black font-[family-name:var(--font-manrope)]">
              <Image src="/user-heart-line.svg" alt="" width={16} height={16} className="size-4" />
              {forum.members} членови
            </p>
          </div>
        </div>

        <button
          type="button"
          aria-label={`Следи го форумот ${forum.name}`}
          className="flex h-10 w-24 cursor-pointer items-center justify-center rounded-xl bg-[#582FF5] px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#3300F5]"
        >
          Следи
        </button>
      </div>

      <p className="font-[family-name:var(--font-manrope)] text-[14px] leading-[20px] text-[#808080]">
        {forum.description}
      </p>
    </article>
  );
}
