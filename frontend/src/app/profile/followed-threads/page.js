import ProfileFollowedThreads from "@/components/profile/ProfileFollowedThreads";

export default function ProfileFollowedThreadsPage() {
  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Дискусии што ги следиш
        </h2>
        <ProfileFollowedThreads />
      </section>
    </div>
  );
}
