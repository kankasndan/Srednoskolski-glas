"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import { usePathname } from "next/navigation";
import Header from "@/components/Header";
import SchoolForums from "@/components/SchoolForums";
import SidebarNav from "@/components/SidebarNav";
import ThematicForums from "@/components/ThematicForums";
import { useForums } from "@/hooks/useForums";

let sidebarScrollTop = 0;
let sidebarCollapsed = false;

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
  const [collapsed, setCollapsed] = useState(sidebarCollapsed);
  const [navOverride, setNavOverride] = useState({ key: null, pathname: null });

  useEffect(() => {
    sidebarCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    setCollapsed(sidebarCollapsed);
  }, []);

  function toggleCollapsed() {
    sidebarCollapsed = !collapsed;
    setCollapsed(sidebarCollapsed);
    localStorage.setItem("sidebarCollapsed", String(sidebarCollapsed));
  }
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
    <div className="flex h-screen flex-col overflow-hidden bg-white">
      <Header />
      <div className="flex min-h-0 flex-1 px-6">
        <aside className="box-border flex shrink-0 flex-col border-r border-[#CCCCCC] pr-6 pt-1">
          <button
            type="button"
            onClick={toggleCollapsed}
            aria-label={collapsed ? "Прошири мени" : "Собери мени"}
            className="mb-1 flex size-10 shrink-0 items-center justify-center"
          >
            <Image
              src="/collapsed icons/menu-collapse.png"
              alt=""
              width={24}
              height={24}
              className={`size-6 ${collapsed ? "rotate-180" : ""}`}
            />
          </button>
          <div
            ref={(node) => {
              if (node) node.scrollTop = sidebarScrollTop;
            }}
            onScroll={(event) => {
              sidebarScrollTop = event.currentTarget.scrollTop;
            }}
            className="min-h-0 flex-1 overflow-y-auto overscroll-contain [scrollbar-width:none] [&::-webkit-scrollbar]:hidden mask-fade-out pb-4"
          >
            <SidebarNav
              selectedKey={selectedKey}
              onSelect={handleSelect}
              collapsed={collapsed}
            />
            <ThematicForums
              forums={general}
              loading={loading}
              error={error}
              selectedKey={selectedKey}
              onSelect={handleSelect}
              collapsed={collapsed}
            />
            {!collapsed && (
              <SchoolForums
                schoolsByCity={schoolsByCity}
                loading={loading}
                error={error}
                selectedKey={selectedKey}
                onSelect={handleSelect}
              />
            )}
          </div>
        </aside>
        <main className={`flex flex-1 items-start justify-center overflow-y-auto pb-12 pt-10 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden ${contentClassName}`}>
          {children}
        </main>
      </div>
    </div>
  );
}
