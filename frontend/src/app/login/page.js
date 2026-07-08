"use client";

export default function LogIn() {
  function handleGoogleLogin() {
    window.location.href = "http://127.0.0.1:8000/auth/google/callback"; // Ova treba da nosi na ruta /api/auth/google/redirect, drugoto e okej
  }

  function handleFacebookLogin() {
    window.location.href = "http://127.0.0.1:8000/auth/facebook/callback"; // Ova treba da nosi na ruta /api/auth/facebook/redirect
  }

  return (
    <main className="p-6 flex flex-col gap-4">
      <button onClick={handleGoogleLogin}>Login with Google</button>
      <button onClick={handleFacebookLogin}>Login with Facebook</button>
    </main>
  );
}
