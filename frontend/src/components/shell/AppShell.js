"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import { usePathname } from "next/navigation";
import Header from "@/components/shell/Header";
import SchoolForums from "@/components/forum/SchoolForums";
import SidebarNav from "@/components/shell/SidebarNav";
import ThematicForums from "@/components/forum/ThematicForums";
import { useForums } from "@/hooks/useForums";

let sidebarScrollTop = 0;
let sidebarCollapsed = false;

function getSelectedKey(pathname) {
  if (pathname === "/feed") return "nav:home";
  if (pathname === "/newest") return "nav:latest";
  if (pathname === "/explore" || pathname === "/search" || pathname?.startsWith("/search?")) {
    return "nav:explore";
  }
  if (pathname?.startsWith("/p/")) {
    const slug = pathname.split("/")[2];
    if (slug) return `forum:${slug}`;
  }
  return null;
}

export default function AppShell({ children, contentClassName = "" }) {
  const pathname = usePathname();
  const { general, schoolsByCity, loading, error } = useForums();
  const [collapsed, setCollapsed] = useState(sidebarCollapsed);
  const [navOverride, setNavOverride] = useState({ key: null, pathname: null });
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

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
  const showCollapsedIcons = collapsed && !mobileMenuOpen;

  function handleSelect(key) {
    setNavOverride({
      key,
      pathname,
    });
    setMobileMenuOpen(false);
  }

  return (
    <div className="flex h-screen flex-col overflow-hidden bg-white">
      <Header
        onMenuToggle={() => setMobileMenuOpen((open) => !open)}
        menuOpen={mobileMenuOpen}
      />
      <div className="flex min-h-0 flex-1 px-4 lg:px-6">
        {mobileMenuOpen && (
          <div
            onClick={() => setMobileMenuOpen(false)}
            aria-hidden="true"
            className="fixed inset-0 z-30 bg-black/40 lg:hidden"
          />
        )}
        <aside
          className={`box-border flex-col border-r border-[#CCCCCC] bg-white pr-6 pt-1 pl-8 lg:static lg:z-auto lg:flex lg:shrink-0 ${
            mobileMenuOpen ? "fixed inset-y-0 left-0 z-40 flex" : "hidden"
          }`}
        >
          <button
            type="button"
            onClick={toggleCollapsed}
            aria-label={collapsed ? "Прошири мени" : "Собери мени"}
            className="mb-1 hidden size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg transition-colors hover:bg-gray-100 lg:flex"
          >
            <Image
              src="/collapsed icons/menu-collapse.svg"
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
              collapsed={showCollapsedIcons}
            />
            <ThematicForums
              forums={general}
              loading={loading}
              error={error}
              selectedKey={selectedKey}
              onSelect={handleSelect}
              collapsed={showCollapsedIcons}
            />
            {!showCollapsedIcons && (
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
        <main
          className={`flex flex-1 items-start justify-center overflow-y-auto px-2 pb-8 pt-4 [scrollbar-width:none] lg:px-0 lg:pb-12 lg:pt-12 [&::-webkit-scrollbar]:hidden ${contentClassName}`}
        >
          {children}
        </main>
      </div>
    </div>
  );
}
