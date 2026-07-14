import NavItem from "@/components/NavItem";

const NAV_ITEMS = [
  { key: "nav:home", label: "Почетна", href: "/feed" },
  { key: "nav:latest", label: "Најнови дискусии" },
  { key: "nav:explore", label: "Истражи" },
];

export default function SidebarNav({ selectedKey, onSelect }) {
  return (
    <nav className="flex w-[268px] flex-col gap-2">
      {NAV_ITEMS.map((item) => (
        <NavItem
          key={item.key}
          label={item.label}
          href={item.href}
          active={selectedKey === item.key}
          onSelect={() => onSelect(item.key)}
        />
      ))}
    </nav>
  );
}
