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
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex px-14 pt-8">
        <div className="sticky top-40 flex h-[calc(100vh-160px)] shrink-0">
          <button
            type="button"
            onClick={toggleCollapsed}
            aria-label={collapsed ? "Прошири мени" : "Собери мени"}
            className="absolute -top-10 left-0 z-10 flex size-10 items-center justify-center"
          >
            <Image
              src="/collapsed icons/menu-collapse.png"
              alt=""
              width={24}
              height={24}
              className={`size-6 ${collapsed ? "rotate-180" : ""}`}
            />
          </button>
          <aside
            ref={(node) => {
              if (node) node.scrollTop = sidebarScrollTop;
            }}
            onScroll={(event) => {
              sidebarScrollTop = event.currentTarget.scrollTop;
            }}
            className="overflow-y-auto overscroll-contain pr-14 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
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
          </aside>
          <div className="w-px shrink-0 rounded-2xl bg-[#CCCCCC]" />
        </div>
        <main className={`flex flex-1 justify-center ${contentClassName}`}>{children}</main>
      </div>
    </div>
  );
}
