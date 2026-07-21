import Image from "next/image";
import FollowForumButton from "@/components/FollowForumButton";
import StartDiscussionButton from "@/components/StartDiscussionButton";
import { bannerFor } from "@/lib/banners";

export default function ForumBanner({ title, description, icon, slug, type, membersCount }) {
  const bannerUrl = bannerFor(slug, type);

  return (
    <section className="w-[990px] max-w-full overflow-hidden rounded-3xl border border-[#CFE9ED] bg-[#DEDFD9]">
      <div
        className="h-[164px] w-full bg-[#DEDFD9] bg-cover bg-center"
        style={{ backgroundImage: `url("${bannerUrl}")` }}
      />

      <div className="flex h-[137px] items-center justify-between gap-[121px] border-t border-[#CFE9ED] bg-white py-6 pl-6 pr-[25px]">
        <div className="flex min-w-0 items-center gap-6">
          <span className="relative size-20 shrink-0 overflow-hidden">
            <Image
              src={icon}
              alt=""
              width={205}
              height={205}
              className="absolute left-[calc(50%+4px)] top-1/2 size-[205px] max-w-none -translate-x-1/2 -translate-y-1/2"
              priority
            />
          </span>

          <div className="flex min-w-0 flex-col gap-2">
            <h1 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-[27px] text-black">
              {title}
            </h1>

            <p className="flex items-center gap-2 font-[family-name:var(--font-oswald)] text-[14px] font-bold leading-none text-black">
              <Image
                src="/user-heart-line.svg"
                alt=""
                width={16}
                height={16}
                className="size-4"
              />
              {membersCount} членови
            </p>

            <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
              {description}
            </p>
          </div>
        </div>

        <div className="flex shrink-0 flex-col gap-2">
          <StartDiscussionButton />
          <FollowForumButton />
        </div>
      </div>
    </section>
  );
}
