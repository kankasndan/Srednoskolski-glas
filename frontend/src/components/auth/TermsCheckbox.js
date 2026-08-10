import Link from "next/link";
import Checkbox from "@/components/ui/Checkbox";

// Se otvoraat vo nov tab za da ne se izgubi popolnetiot formular.
function LegalLink({ href, children }) {
  return (
    <Link
      href={href}
      target="_blank"
      className="underline underline-offset-2 hover:text-[var(--color-primary-200)]"
    >
      {children}
    </Link>
  );
}

export default function TermsCheckbox({ checked, onChange }) {
  return (
    <Checkbox checked={checked} onChange={onChange} required>
      <span className="font-(family-name:--font-manrope) text-[12px] font-normal leading-[19.4px] text-[#595959] 2xl:text-[14px]">
        Се согласувам со <LegalLink href="/terms">Условите за користење</LegalLink>{" "}
        и <LegalLink href="/privacy">Политиката на приватност</LegalLink>
      </span>
    </Checkbox>
  );
}
