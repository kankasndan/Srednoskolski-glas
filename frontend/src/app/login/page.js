"use client";

export default function LogIn() {
  return (
    <main className="p-6 flex flex-col gap-4">
      <button onClick={handleGoogleLogin}>Login with Google</button>
      <button onClick={handleFacebookLogin}>Login with Facebook</button>
    </main>
  );
}