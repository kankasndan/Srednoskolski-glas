"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
// import { faGoogle } from "@fortawesome/free-solid-svg-icons";
import { faGoogle, faFacebook } from "@fortawesome/free-brands-svg-icons";

config.autoAddCss = false;

export default function LogIn() {
  return (
    <main className="box-border min-h-screen w-full p-6 flex flex-col items-center justify-center">
      <div className="flex flex-col gap-4 items-center p-6 rounded-4xl border border-gray-200 shadow-xl">
        <h1 className="text-xl font-bold">Log in with:</h1>
        <a
          href={process.env.NEXT_PUBLIC_API_URL + "/api/auth/google/redirect"}
          className="bg-blue-500 px-6 py-2 rounded-xl text-white hover:bg-blue-400 transition"
        >
          <FontAwesomeIcon icon={faGoogle} /> Login with Google
        </a>
        <a
          href={process.env.NEXT_PUBLIC_API_URL + "/api/auth/google/redirect"}
          className="bg-blue-500 px-6 py-2 rounded-xl text-white hover:bg-blue-400 transition"
        >
          <FontAwesomeIcon icon={faFacebook} /> Login with Facebook
        </a>
      </div>
    </main>
  );
}
