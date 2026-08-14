"use client";

import Image from "next/image";
import SchoolForums from "@/components/forum/SchoolForums";
import SidebarNav from "@/components/shell/SidebarNav";
import ThematicForums from "@/components/forum/ThematicForums";
import { useModalDismiss } from "@/hooks/useModalDismiss";

export default function MobileMenu({
  open,
  onClose,
  general,
  schoolsByCity,
  loading,
  error,
  selectedKey,
  onSelect,
}) {
  useModalDismiss(open, onClose);

  return (
    <div
      aria-hidden={!open}
      className={`fixed inset-0 z-[60] lg:hidden ${open ? "" : "pointer-events-none"}`}
    >
      <div
        onClick={onClose}
        className={`absolute inset-0 bg-black/40 transition-opacity duration-300 ${
          open ? "opacity-100" : "opacity-0"
        }`}
      />

      <div
        className={`relative flex h-full w-[315px] max-w-[85%] flex-col rounded-r-[40px] bg-white pt-4 transition-transform duration-300 ease-out ${
          open ? "translate-x-0" : "-translate-x-full"
        }`}
      >
        <button
          type="button"
          onClick={onClose}
          aria-label="Затвори мени"
          className="ml-6 flex size-8 shrink-0 cursor-pointer items-center justify-center"
        >
          <Image
            src="/mobile version/collabsing menu.svg"
            alt=""
            width={24}
            height={22}
            className="h-auto w-6"
          />
        </button>

        <div className="min-h-0 flex-1 overflow-y-auto px-6 pb-8 pt-6 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
          <SidebarNav selectedKey={selectedKey} onSelect={onSelect} collapsed={false} />
          <ThematicForums
            forums={general}
            loading={loading}
            error={error}
            selectedKey={selectedKey}
            onSelect={onSelect}
            collapsed={false}
          />
          <SchoolForums
            schoolsByCity={schoolsByCity}
            loading={loading}
            error={error}
            selectedKey={selectedKey}
            onSelect={onSelect}
          />
        </div>
      </div>
    </div>
  );
}
