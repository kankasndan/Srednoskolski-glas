"use client";

import Image from "next/image";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";
import { useProfile } from "@/hooks/useProfile";
import { canCreateThreads } from "@/lib/capabilities";

export default function CommunityBanner() {
  const { user, loading } = useProfile();
  const showCreate = !loading && canCreateThreads(user);

  return (
    <section className="flex h-32 w-[990px] max-w-full items-center gap-[122px] rounded-3xl bg-[#CFE9ED] py-6 pl-6 pr-[22px]">
      <div className="flex h-20 w-[554px] shrink-0 items-center gap-[15px]">
        <Image
          src="/logo.svg"
          alt=""
          width={119}
          height={80}
          className="h-20 w-[119px] shrink-0 object-contain"
          priority
        />

        <div className="flex h-[60px] w-[447px] shrink-0 flex-col gap-2">
          <h2 className="h-[30px] w-[420px] font-[family-name:var(--font-oswald)] text-[20px] font-bold leading-none text-black">
            МЕСТО КАДЕ СЕКОЈ СРЕДНОШКОЛЕЦ ИМА ГЛАС
          </h2>
          <p className="h-[22px] w-[447px] font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-none text-[#595959]">
            {showCreate
              ? "Прашувај, споделувај и откриј што мислат твоите врсници."
              : "Придружи се на разговорите во заедницата."}
          </p>
        </div>
      </div>

      <StartDiscussionButton />
    </section>
  );
}
