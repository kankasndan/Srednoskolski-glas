import NavItem from "@/components/shell/NavItem";

const NAV_ITEMS = [
  { key: "nav:home", label: "Почетна", href: "/feed", icon: "/collapsed icons/home.png" },
  { key: "nav:explore", label: "Истражи", href: "/explore", icon: "/collapsed icons/search.png" },
];

export default function SidebarNav({ selectedKey, onSelect, collapsed }) {
  return (
    <nav className={`flex flex-col gap-2 transition-[width] duration-300 ease-in-out ${collapsed ? "w-10" : "w-[268px]"}`}>
      {NAV_ITEMS.map((item) => (
        <NavItem
          key={item.key}
          label={item.label}
          href={item.href}
          icon={item.icon}
          active={selectedKey === item.key}
          onSelect={() => onSelect(item.key)}
          collapsed={collapsed}
        />
      ))}
    </nav>
  );
}
