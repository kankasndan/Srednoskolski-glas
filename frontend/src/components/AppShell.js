"use client";

import { useState } from "react";
import { usePathname } from "next/navigation";
import Header from "@/components/Header";
import SchoolForums from "@/components/SchoolForums";
import SidebarNav from "@/components/SidebarNav";
import ThematicForums from "@/components/ThematicForums";

function getSelectedKey(pathname) {
  if (pathname === "/feed") return "nav:home";
  if (pathname?.startsWith("/p/")) {
    const slug = pathname.split("/")[2];
    if (slug) return `forum:${slug}`;
  }
  return null;
}

export default function AppShell({ children, contentClassName = "pl-8" }) {
  const pathname = usePathname();
  const [navOverride, setNavOverride] = useState({ key: null, pathname: null });
  const selectedKey =
    navOverride.pathname === pathname && navOverride.key
      ? navOverride.key
      : getSelectedKey(pathname);

  function handleSelect(key) {
    setNavOverride({
      key: key.startsWith("nav:") ? key : null,
      pathname,
    });
  }

  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex px-14 pt-8">
        <div className="sticky top-40 flex h-[calc(100vh-160px)] shrink-0">
          <aside className="overflow-y-auto overscroll-contain pr-14 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <SidebarNav selectedKey={selectedKey} onSelect={handleSelect} />
            <ThematicForums selectedKey={selectedKey} onSelect={handleSelect} />
            <SchoolForums selectedKey={selectedKey} onSelect={handleSelect} />
          </aside>
          <div className="w-px shrink-0 rounded-2xl bg-[#CCCCCC]" />
        </div>
        <div className="w-px self-stretch rounded-2xl bg-[#CCCCCC]" />
        <main className={`flex flex-1 justify-center ${contentClassName}`}>{children}</main>
      </div>
    </div>
  );
}
