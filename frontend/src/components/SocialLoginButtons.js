"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faApple, faFacebookF, faGoogle } from "@fortawesome/free-brands-svg-icons";
import { useEffect, useState } from "react";

config.autoAddCss = false;

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

const socialProviders = [
  { id: "google", label: "Најави се со Google", icon: faGoogle },
  { id: "facebook", label: "Најави се со Facebook", icon: faFacebookF },
];

const errorMessages = {
  auth_failed: "Најавата не успеа. Обиди се повторно.",
  missing_token: "Најавата не успеа. Обиди се повторно.",
};

export default function SocialLoginButtons() {
  const [errorMessage, setErrorMessage] = useState("");

  useEffect(() => {
    const errorCode = new URLSearchParams(window.location.search).get("error");
    if (errorCode) {
      setErrorMessage(errorMessages[errorCode] || errorMessages.auth_failed);
    }
  }, []);

  return (
    <div className="mt-16">
      {errorMessage && (
        <p
          role="alert"
          className="mb-8 rounded-xl bg-red-50 px-4 py-3 text-center font-(family-name:--font-manrope) text-sm font-medium text-red-600"
        >
          {errorMessage}
        </p>
      )}

      <div className="mx-auto flex w-full max-w-100 flex-col gap-4">
        {socialProviders.map((provider) => (
          <a
            key={provider.id}
            href={`${API_BASE_URL}/api/auth/${provider.id}/redirect`}
            className="flex h-14 items-center justify-center gap-3 rounded-2xl bg-[#582FF5] px-[25.82px] py-[12.91px] font-(family-name:--font-manrope) text-[16px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0]"
          >
            <FontAwesomeIcon icon={provider.icon} className="text-[32px]" />
            {provider.label}
          </a>
        ))}

        <button
          type="button"
          disabled
          className="flex h-14 cursor-not-allowed items-center justify-center gap-3 rounded-2xl bg-gray-300 px-[25.82px] py-[12.91px] font-(family-name:--font-manrope) text-[16px] font-bold leading-none text-white"
        >
          <FontAwesomeIcon icon={faApple} className="text-[32px]" />
          Наскоро
        </button>
      </div>

      <p className="mx-auto mt-25 max-w-sm text-center font-(family-name:--font-manrope) text-[16px] font-normal leading-[19.37px] text-[#999999]">
        Никогаш нема да објавиме ништо на твоите профили без твоја дозвола.
      </p>
    </div>
  );
}
