import Link from "next/link";

export default function Home() {
  return (
    <main className="p-6 flex flex-col gap-4">
      <h1>Srednoskolski Glas</h1>
      <Link href="/register">
        <button>Login</button>
      </Link>
    </main>
  );
}
