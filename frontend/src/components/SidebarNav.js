"use client";

import { useState } from "react";
import NavItem from "@/components/NavItem";

const NAV_ITEMS = [
  { label: "Почетна" },
  { label: "Најнови дискусии" },
  { label: "Пребарај дискусии" },
  { label: "Започни дискусија", variant: "plus" },
];

export default function SidebarNav() {
  const [selected, setSelected] = useState(0);

  return (
    <nav className="flex w-[268px] flex-col gap-2">
      {NAV_ITEMS.map((item, index) => (
        <NavItem
          key={item.label}
          label={item.label}
          variant={item.variant}
          selected={selected === index}
          onClick={() => setSelected(index)}
        />
      ))}
    </nav>
  );
}
