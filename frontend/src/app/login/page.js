import LoginHero from "@/components/LoginHero";
import LoginBackButton from "@/components/LoginBackButton";
import LoginMasthead from "@/components/LoginMasthead";
import SocialLoginButtons from "@/components/SocialLoginButtons";

export default function LogIn() {
  return (
    <main className="flex min-h-screen w-full bg-white">
      <LoginHero />

      <div className="relative flex w-full flex-col px-6 py-8 lg:w-1/2 lg:px-16">
        <LoginBackButton />

        <div className="mx-auto flex min-h-157 w-full max-w-122 flex-1 flex-col justify-center py-10">
          <LoginMasthead />
          <SocialLoginButtons />
        </div>
      </div>
    </main>
  );
}
