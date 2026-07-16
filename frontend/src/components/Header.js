import Image from "next/image";
import Link from "next/link";
import SearchBar from "@/components/SearchBar";
import AuthButtons from "@/components/AuthButtons";

export default function Header() {
  return (
    <header className="sticky top-0 z-50 flex h-32 w-full items-center gap-20 bg-white px-14 pb-6 pt-12">
      <Link
        href="/feed"
        className="flex h-14 w-60 shrink-0 items-center justify-center gap-3 overflow-hidden"
      >
        <Image
          src="/logo-with-text.svg?v=large-header"
          alt="Средношколски глас"
          width={240}
          height={56}
          priority
          className="block h-14 w-60"
        />
      </Link>
      <SearchBar />
      <AuthButtons />
    </header>
  );
}
