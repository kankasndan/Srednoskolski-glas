"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

export default function AuthCallbackPage() {
  const router = useRouter();

  useEffect(() => {
    const token = new URLSearchParams(window.location.search).get("token");

    if (token) {
      localStorage.setItem("auth_token", token);
      router.replace("/feed");
      return;
    }

    router.replace("/login?error=missing_token");
  }, [router]);

  return (
    <main className="box-border min-h-screen w-full p-6 flex items-center justify-center">
      <p>Signing you in...</p>
    </main>
  );
}