import NavItem from "@/components/NavItem";

export default function SidebarNav() {
  return (
    <nav className="flex w-[268px] flex-col gap-2">
      <NavItem label="Почетна" defaultChecked />
      <NavItem label="Најнови дискусии" />
      <NavItem label="Пребарај дискусии" />
      <NavItem label="Започни дискусија" variant="plus" />
    </nav>
  );
}
