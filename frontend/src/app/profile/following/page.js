import ProfileFollowedForums from "@/components/profile/ProfileFollowedForums";

export default function ProfileFollowingPage() {
  return (
    <div className="flex flex-col gap-12">
      <section className="flex flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Форуми што ги следиш
        </h2>
        <ProfileFollowedForums />
      </section>
    </div>
  );
}
