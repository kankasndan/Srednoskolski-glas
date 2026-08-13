import Image from "next/image";
import Link from "next/link";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";
import { useProfile } from "@/hooks/useProfile";
import { canCreateThreads, needsOnboarding } from "@/lib/capabilities";

export default function CommunityBanner() {
  const { user, loading } = useProfile();
  const incomplete = !loading && needsOnboarding(user);
  const showCreate = !loading && canCreateThreads(user);

  return (
    <section className="flex min-h-32 w-[990px] max-w-full items-center justify-between gap-6 rounded-3xl bg-[#CFE9ED] py-6 pl-6 pr-[22px]">
      <div className="flex min-w-0 flex-1 items-center gap-[15px]">
        <Image
          src="/logo.svg"
          alt=""
          width={119}
          height={80}
          className="hidden h-20 w-[119px] shrink-0 object-contain lg:block"
          priority
        />

        <div className="flex min-w-0 flex-1 flex-col gap-2">
          <h2 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold leading-none text-black">
            МЕСТО КАДЕ СЕКОЈ СРЕДНОШКОЛЕЦ ИМА ГЛАС
          </h2>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            {incomplete
              ? "Заврши ја регистрацијата за да објавуваш и да се приклучиш на разговорите."
              : showCreate
                ? "Прашувај, споделувај и откриј што мислат твоите врсници."
                : "Придружи се на разговорите во заедницата."}
          </p>
        </div>
      </div>

      {incomplete ? (
        <Link
          href="/register/onboarding"
          className="flex h-10 w-[268px] shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0]"
        >
          Заврши регистрација
        </Link>
      ) : (
        <StartDiscussionButton />
      )}
    </section>
  );
}
