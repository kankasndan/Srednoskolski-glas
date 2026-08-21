"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { updateProfile } from "@/api/profile";
import { finishOnboarding, readGeneratedAvatar } from "@/lib/onboardingFlow";
import { loadSessionUser, setSessionUser } from "@/lib/sessionUser";
import PrimaryButton from "@/components/ui/PrimaryButton";

const FALLBACK_AVATAR = "/user-line.svg";

export default function AvatarReadyCard() {
  const router = useRouter();
  const [avatarUrl, setAvatarUrl] = useState(null);
  const [imageFailed, setImageFailed] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    let cancelled = false;
    const storedAvatar = readGeneratedAvatar();
    const avatar = storedAvatar
      ? Promise.resolve(storedAvatar)
      : loadSessionUser({ force: true }).then((user) => user?.imageUrl ?? null);

    avatar.then((nextAvatarUrl) => {
      if (cancelled) return;
      setAvatarUrl(nextAvatarUrl);
    });

    return () => {
      cancelled = true;
    };
  }, []);

  function handleUseAvatar() {
    setSubmitting(true);
    finishOnboarding(router);
  }

  async function handleContinueWithoutAvatar() {
    setSubmitting(true);
    setError("");

    try {
      const user = await updateProfile({ image_url: "" });
      setSessionUser(user);
      finishOnboarding(router);
    } catch {
      setError("Не успеавме да продолжиме без аватар. Обиди се повторно.");
      setSubmitting(false);
    }
  }

  return (
    <div className="relative z-10 flex w-full max-w-[342px] flex-col items-center sm:max-w-[533px]">
      <div className="relative size-[180px] overflow-hidden rounded-full bg-[#CCCCCC] sm:size-[260px] lg:size-[361px]">
        <Image
          src={imageFailed || !avatarUrl ? FALLBACK_AVATAR : avatarUrl}
          alt="Твојот генериран аватар"
          fill
          sizes="(max-width: 639px) 180px, (max-width: 1023px) 260px, 361px"
          className="object-cover"
          priority
          onError={() => setImageFailed(true)}
        />
      </div>

      <div className="mt-8 flex w-full flex-col items-center gap-4 sm:gap-5 lg:gap-6">
        <h1 className="text-center font-(family-name:--font-manrope) text-[20px] font-bold leading-none text-[#582FF5] sm:text-[22px] lg:text-[24px]">
          Твојот аватар е готов!
        </h1>

        <p className="max-w-[342px] text-center font-(family-name:--font-manrope) text-[16px] font-normal leading-none text-black sm:max-w-[533px] sm:text-[18px] lg:text-[20px] lg:leading-[22.59px]">
          Тој е уникатен и создаден само за тебе - ниту еден друг корисник нема
          да има идентичен аватар.
        </p>
      </div>

      {error ? (
        <p className="mt-6 text-center font-(family-name:--font-manrope) text-[13px] text-[#DC2626]">
          {error}
        </p>
      ) : null}

      <PrimaryButton
        type="button"
        onClick={handleUseAvatar}
        disabled={submitting}
        className="mt-12 h-10 w-full rounded-[200px] font-(family-name:--font-manrope) text-[14px] disabled:cursor-wait disabled:bg-[var(--color-grays-300)] disabled:hover:bg-[var(--color-grays-300)] sm:mt-16 sm:h-12 sm:max-w-[400px] sm:text-[15px] lg:mt-20 lg:h-14 lg:text-[16px]"
      >
        Користи аватар
      </PrimaryButton>

      <button
        type="button"
        onClick={handleContinueWithoutAvatar}
        disabled={submitting}
        className="mt-6 cursor-pointer text-center font-(family-name:--font-manrope) text-[14px] font-normal leading-none text-[#595959] transition-colors hover:text-[#333333] disabled:cursor-wait disabled:opacity-50 lg:text-[16px]"
      >
        Продолжи без аватар
      </button>
    </div>
  );
}
