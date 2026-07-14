export default function TermsCheckbox({ checked, onChange }) {
  return (
    <label className="flex items-center gap-2">
      <input
        type="checkbox"
        checked={checked}
        onChange={onChange}
        required
        className="h-4 w-4 shrink-0 accent-[#582FF5] 2xl:h-5 2xl:w-5"
      />
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
    </label>
  );
}
