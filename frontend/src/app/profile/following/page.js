import ProfileFollowedForums from "@/components/ProfileFollowedForums";
import ProfileFollowedThreads from "@/components/ProfileFollowedThreads";

export default function ProfileFollowingPage() {
  return (
    <div className="flex flex-col gap-12">
      <section className="flex flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Форуми што ги следиш
        </h2>
        <ProfileFollowedForums />
      </section>

      <section className="flex flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Дискусии што ги следиш
        </h2>
        <ProfileFollowedThreads />
      </section>
    </div>
  );
}
