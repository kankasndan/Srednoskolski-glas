"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faApple, faFacebookF, faGoogle } from "@fortawesome/free-brands-svg-icons";
import { useEffect, useState } from "react";
import { API_BASE_URL } from "@/lib/api";

config.autoAddCss = false;

const socialProviders = [
  { id: "google", label: "Најави се со Google", icon: faGoogle },
  { id: "facebook", label: "Најави се со Facebook", icon: faFacebookF },
];

const errorMessages = {
  auth_failed: "Најавата не успеа. Обиди се повторно.",
  missing_token: "Најавата не успеа. Обиди се повторно.",
};

export default function SocialAuthButtons({ successRedirect }) {
  const [errorMessage, setErrorMessage] = useState("");

  useEffect(() => {
    const errorCode = new URLSearchParams(window.location.search).get("error");
    if (errorCode) {
      setErrorMessage(errorMessages[errorCode] || errorMessages.auth_failed);
    }
  }, []);

  function handleClick() {
    localStorage.setItem("post_login_redirect", successRedirect);
    localStorage.setItem("post_login_error_redirect", window.location.pathname);
  }

  return (
    <div className="mt-12">
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
            onClick={handleClick}
            className="flex h-12 items-center justify-center gap-3 rounded-2xl bg-[#582FF5] px-6 font-(family-name:--font-manrope) text-[15px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0] 2xl:h-14 2xl:text-[17px]"
          >
            <FontAwesomeIcon icon={provider.icon} className="text-[22px] 2xl:text-[26px]" />
            {provider.label}
          </a>
        ))}

        <button
          type="button"
          disabled
          className="flex h-12 cursor-not-allowed items-center justify-center gap-3 rounded-2xl bg-gray-300 px-6 font-(family-name:--font-manrope) text-[15px] font-bold leading-none text-white 2xl:h-14 2xl:text-[17px]"
        >
          <FontAwesomeIcon icon={faApple} className="text-[22px] 2xl:text-[26px]" />
          Наскоро
        </button>
      </div>

      <p className="mx-auto mt-12 max-w-sm text-center font-(family-name:--font-manrope) text-[13px] font-normal leading-[16px] text-[#999999] 2xl:max-w-[440px] 2xl:text-[15px] 2xl:leading-[18px]">
        Никогаш нема да објавиме ништо на твоите профили без твоја дозвола.
      </p>
    </div>
  );
}
