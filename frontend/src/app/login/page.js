"use client";

export default function LogIn() {
  function handleGoogleLogin() {
    window.location.href =
      process.env.NEXT_PUBLIC_API_URL + "/api/auth/google/redirect"; // process.env.NEXT_PUBLIC_API_URL must be used everywhere
  }

  function handleFacebookLogin() {
    window.location.href =
      process.env.NEXT_PUBLIC_API_URL + "/api/auth/facebook/redirect";
  }

  return (
    <main className="p-6 flex flex-col gap-4">
      <button onClick={handleGoogleLogin}>Login with Google</button>
      <button onClick={handleFacebookLogin}>Login with Facebook</button>
    </main>
  );
}
