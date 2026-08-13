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
    <Checkbox
      checked={checked}
      onChange={onChange}
      required
      className="h-8 w-[295px] items-center gap-2"
      boxClassName="size-6 rounded-lg"
      checkClassName="w-3"
    >
      <span className="h-8 w-[263px] font-(family-name:--font-manrope) text-[12px] font-normal leading-4 text-[#595959]">
        Се согласувам со <LegalLink href="/terms">Условите за користење</LegalLink>{" "}
        и <LegalLink href="/privacy">Политиката на приватност</LegalLink><span className="text-red-500">*</span>
      </span>
    </Checkbox>
  );
}
