import Image from "next/image";
import Link from "next/link";
import SearchBar from "@/components/SearchBar";
import AuthButtons from "@/components/AuthButtons";

export default function Header() {
  return (
    <header className="sticky top-0 z-50 flex h-32 w-full items-center gap-20 bg-white pt-12 pr-14 pb-6 pl-14">
      <Link
        href="/feed"
        className="flex h-14 w-60 shrink-0 items-center justify-center overflow-hidden"
      >
        <Image
          src="/logo.svg"
          alt="Средношколски глас"
          width={240}
          height={56}
          priority
          className="h-14 w-60 object-cover"
        />
      </Link>
      <SearchBar />
      <AuthButtons />
    </header>
  );
}
