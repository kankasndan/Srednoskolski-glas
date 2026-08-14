"use client";

import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faFacebookF, faGoogle } from "@fortawesome/free-brands-svg-icons";
import { useEffect, useState } from "react";
import Link from "next/link";
import { API_BASE_URL } from "@/lib/api";
import { safeInternalPath } from "@/lib/paths";

const socialProviders = [
  { id: "google", name: "Google", icon: faGoogle },
  { id: "facebook", name: "Facebook", icon: faFacebookF },
];

const errorMessages = {
  auth_failed: "Најавата не успеа. Обиди се повторно.",
  missing_token: "Најавата не успеа. Обиди се повторно.",
  email_in_use:
    "Оваа е-пошта е веќе поврзана со друг профил. Најави се со истиот начин како при регистрација.",
};

export default function SocialAuthButtons({
  successRedirect,
  actionLabel = "Најави се со",
  variant = "default",
  className = "",
}) {
  const [errorMessage, setErrorMessage] = useState("");

  useEffect(() => {
    queueMicrotask(() => {
      const errorCode = new URLSearchParams(window.location.search).get("error");
      if (errorCode) {
        setErrorMessage(errorMessages[errorCode] || errorMessages.auth_failed);
      }
    });
  }, []);

  function handleClick() {
    localStorage.setItem("post_login_redirect", safeInternalPath(successRedirect));
    localStorage.setItem("post_login_error_redirect", window.location.pathname);
  }

  const mobileProviders = [socialProviders[1], socialProviders[0]];
  const isMobileAuthVariant = variant === "register" || variant === "loginMobile";
  const providers = isMobileAuthVariant ? mobileProviders : socialProviders;

  if (isMobileAuthVariant) {
    return (
      <>
        <div className="v-stack mt-[67px] w-full items-center lg:hidden">
          {errorMessage && (
            <p
              role="alert"
              className="mb-6 w-full rounded-xl bg-red-50 px-4 py-3 text-center font-(family-name:--font-manrope) text-sm font-medium text-red-600"
            >
              {errorMessage}
            </p>
          )}

          <div className="v-stack w-full gap-2">
            {providers.map((provider) => (
              <a
                key={provider.id}
                href={`${API_BASE_URL}/api/auth/${provider.id}/redirect`}
                onClick={handleClick}
                className="h-stack h-14 w-full items-center justify-center gap-4 rounded-[16px] bg-[#582FF5] px-4 py-2 font-(family-name:--font-manrope) text-base leading-none font-bold text-white transition-colors hover:bg-[#4B25E0]"
              >
                <FontAwesomeIcon icon={provider.icon} className="w-9 text-[22px]" />
                <span className="min-w-0">{`${actionLabel} ${provider.name}`}</span>
              </a>
            ))}
          </div>

          <p className="mt-9 w-full max-w-[266px] text-center font-(family-name:--font-manrope) text-xs leading-none font-normal text-[#595959]">
            Никогаш нема да објавиме ништо на твоите профили без твоја дозвола.
          </p>

          {variant === "register" ? (
            <LoginPrompt className="mt-24 sm:mt-28 md:mt-32" />
          ) : (
            <RegisterPrompt className="mt-24 sm:mt-28 md:mt-32" />
          )}
        </div>

        <div className="hidden lg:block">
          <DefaultSocialAuthButtons
            actionLabel={actionLabel}
            errorMessage={errorMessage}
            onProviderClick={handleClick}
            className={className}
            variant={variant}
          />
        </div>
      </>
    );
  }

  return (
    <DefaultSocialAuthButtons
      successRedirect={successRedirect}
      actionLabel={actionLabel}
      errorMessage={errorMessage}
      onProviderClick={handleClick}
      className={className}
    />
  );
}

function RegisterPrompt({ className = "" }) {
  return (
    <p
      className={`text-center font-(family-name:--font-manrope) text-sm leading-none font-normal text-[#737373] ${className}`}
    >
      Немаш профил?{" "}
      <Link href="/register" className="font-bold text-[#582FF5]">
        Регистрирај се
      </Link>
    </p>
  );
}

function LoginPrompt({ className = "" }) {
  return (
    <p
      className={`text-center font-(family-name:--font-manrope) text-sm leading-none font-normal text-[#737373] ${className}`}
    >
      Веќе имаш профил?{" "}
      <Link href="/login" className="font-bold text-[#582FF5]">
        Најави се
      </Link>
    </p>
  );
}

function DefaultSocialAuthButtons({
  actionLabel,
  errorMessage,
  onProviderClick,
  className = "mt-12",
}) {
  return (
    <div className={className}>
      {errorMessage && (
        <p
          role="alert"
          className="mb-6 rounded-xl bg-red-50 px-4 py-3 text-center font-(family-name:--font-manrope) text-sm font-medium text-red-600"
        >
          {errorMessage}
        </p>
      )}

      <div className="mx-auto flex w-full max-w-90 flex-col gap-3 2xl:max-w-[440px] 2xl:gap-4">
        {socialProviders.map((provider) => (
          <a
            key={provider.id}
            href={`${API_BASE_URL}/api/auth/${provider.id}/redirect`}
            onClick={onProviderClick}
            className="flex h-12 cursor-pointer items-center justify-center gap-3 rounded-2xl bg-[#582FF5] px-6 font-(family-name:--font-manrope) text-[15px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0] 2xl:h-14 2xl:text-[17px]"
          >
            <FontAwesomeIcon icon={provider.icon} className="text-[22px] 2xl:text-[26px]" />
            {`${actionLabel} ${provider.name}`}
          </a>
        ))}

      </div>

      <p className="mx-auto mt-12 max-w-sm text-center font-(family-name:--font-manrope) text-[13px] font-normal leading-[16px] text-[#595959] 2xl:max-w-[440px] 2xl:text-[15px] 2xl:leading-[18px]">
        Никогаш нема да објавиме ништо на твоите профили без твоја дозвола.
      </p>
    </div>
  );
}
