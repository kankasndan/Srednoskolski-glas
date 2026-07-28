import Checkbox from "@/components/Checkbox";

export default function TermsCheckbox({ checked, onChange }) {
  return (
    <Checkbox checked={checked} onChange={onChange} required>
      <span className="font-(family-name:--font-manrope) text-[12px] font-normal leading-[19.4px] text-[#595959] 2xl:text-[14px]">
        Се согласувам со{" "}
        <span className="underline underline-offset-2">
          Условите за користење
        </span>{" "}
        и{" "}
        <span className="underline underline-offset-2">
          Политиката на приватност
        </span>
      </span>
    </Checkbox>
  );
}
