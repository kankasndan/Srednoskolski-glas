import AuthHero from "@/components/AuthHero";
import BackButton from "@/components/BackButton";
import AuthMasthead from "@/components/AuthMasthead";
import SocialAuthButtons from "@/components/SocialAuthButtons";

export default function LogIn() {
  return (
    <main className="flex min-h-screen w-full bg-white">
      <AuthHero />

      <div className="relative flex w-full flex-col px-6 py-8 lg:w-1/2 lg:px-16">
        <BackButton />

        <div className="mx-auto flex w-full max-w-122 flex-1 flex-col justify-center py-8 2xl:max-w-[560px]">
          <AuthMasthead
            titleLine="НАЈАВИ СЕ НА"
            subtitle="Најави се за повторно да се поврзеш со заедницата на средношколци во Македонија."
          />
          <SocialAuthButtons successRedirect="/feed" />
        </div>
      </div>
    </main>
  );
}
