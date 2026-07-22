"use client";

import { useState } from "react";
import { usePathname } from "next/navigation";
import Header from "@/components/Header";
import SchoolForums from "@/components/SchoolForums";
import SidebarNav from "@/components/SidebarNav";
import ThematicForums from "@/components/ThematicForums";
import { useForums } from "@/hooks/useForums";

let sidebarScrollTop = 0;

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
  const { general, schoolsByCity, loading, error } = useForums();
  const [navOverride, setNavOverride] = useState({ key: null, pathname: null });
  const selectedKey =
    navOverride.pathname === pathname && navOverride.key
      ? navOverride.key
      : getSelectedKey(pathname);

  function handleSelect(key) {
    setNavOverride({
      key,
      pathname,
    });
  }

  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex px-6">
        <div className="box-border min-h-full shrink-0 pt-10 border-r border-gray-200">
          <aside
            ref={(node) => {
              if (node) node.scrollTop = sidebarScrollTop;
            }}
            onScroll={(event) => {
              sidebarScrollTop = event.currentTarget.scrollTop;
            }}
            className="sticky max-h-[70vh] top-26 pr-6 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden overflow-y-scroll overscroll-contain mask-fade-out pb-20"
          >
            <SidebarNav selectedKey={selectedKey} onSelect={handleSelect} />
            <ThematicForums
              forums={general}
              loading={loading}
              error={error}
              selectedKey={selectedKey}
              onSelect={handleSelect}
            />
            <SchoolForums
              schoolsByCity={schoolsByCity}
              loading={loading}
              error={error}
              selectedKey={selectedKey}
              onSelect={handleSelect}
            />
          </aside>
        </div>
        <main className={`pt-8 flex flex-1 justify-center ${contentClassName}`}>
          {children}
        </main>
      </div>
    </div>
  );
}
