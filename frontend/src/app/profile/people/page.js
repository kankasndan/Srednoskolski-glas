import ProfileFollowedUsers from "@/components/profile/ProfileFollowedUsers";

export default function ProfilePeoplePage() {
  return (
    <div className="flex flex-col gap-12">
      <section className="flex flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Корисници што ги следиш
        </h2>
        <ProfileFollowedUsers />
      </section>
    </div>
  );
}
