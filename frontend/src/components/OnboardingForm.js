"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { API_BASE_URL } from "@/lib/api";
import { CITIES } from "@/lib/schools";
import TextField from "@/components/TextField";
import SelectField from "@/components/SelectField";
import TermsCheckbox from "@/components/TermsCheckbox";
import SubmitButton from "@/components/SubmitButton";

const AREAS = [
  "Геолошко-рударска и металуршка струка",
  "Градежно-геодетска струка",
  "Графичка струка",
  "Економско-правна и трговска струка",
  "Електротехничка струка",
  "Здравствена струка",
  "Земјоделска-ветеринарна струка",
  "Лични услуги",
  "Машинска струка",
  "Сообраќајна струка",
  "Текстилно-кожарска струка",
  "Угостителско-туристичка струка",
  "Хемиско-технолошка струка",
  "Шумарско-дрвопреработувачка струка",
  "ПМА",
  "ПМБ",
  "ОХА",
  "ОХБ",
  "ЈУА",
  "ЈУБ",
  "Друго",
];

const YEARS = ["Прва", "Втора", "Трета", "Четврта"];

const LOCKED_HINT = "Само средношколци можат да го пополнат ова поле.";

export default function OnboardingForm() {
  const router = useRouter();
  const [school, setSchool] = useState("");
  const [area, setArea] = useState("");
  const [year, setYear] = useState("");
  const [notStudent, setNotStudent] = useState(false);
  const [agreed, setAgreed] = useState(false);

  // Cities as group headers, ordered by how many schools each has (most first).
  const schoolGroups = [...CITIES].sort(
    (a, b) => b.schools.length - a.schools.length
  );

  // "Не сум средношколец" clears and locks the school-only fields.
  function handleNotStudentChange(e) {
    const checked = e.target.checked;
    setNotStudent(checked);
    if (checked) {
      setSchool("");
      setArea("");
      setYear("");
    }
  }

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        router.push("/register/onboarding_2");
      }}
      className="mx-auto mt-12 flex w-full max-w-[360px] flex-col gap-3 2xl:max-w-[440px] 2xl:gap-4"
    >
      <TextField
        id="pseudonym"
        label="Псевдоним (3-20 карактери)"
        required
        placeholder="пр. марко_2026"
        minLength={3}
        maxLength={20}
      />

      <label className="flex items-center gap-2">
        <input
          type="checkbox"
          checked={notStudent}
          onChange={handleNotStudentChange}
          className="h-4 w-4 shrink-0 accent-[#582FF5] 2xl:h-5 2xl:w-5"
        />
        <span className="font-(family-name:--font-manrope) text-[12px] font-normal leading-[19.4px] text-[#595959] 2xl:text-[14px]">
          Не сум средношколец
        </span>
      </label>

      <SelectField
        id="school"
        label="Училиште"
        required
        value={school}
        onChange={(e) => setSchool(e.target.value)}
        placeholder="Избери училиште"
        groups={schoolGroups}
        disabled={notStudent}
        tooltip={notStudent ? LOCKED_HINT : undefined}
      />

      <SelectField
        id="area"
        label="Подрачје"
        required
        value={area}
        onChange={(e) => setArea(e.target.value)}
        placeholder="Избери подрачје"
        options={AREAS}
        disabled={notStudent}
        tooltip={notStudent ? LOCKED_HINT : undefined}
      />

      <SelectField
        id="year"
        label="Година"
        required
        value={year}
        onChange={(e) => setYear(e.target.value)}
        placeholder="Избери година"
        options={YEARS}
        disabled={notStudent}
        tooltip={notStudent ? LOCKED_HINT : undefined}
      />

      <TermsCheckbox
        checked={agreed}
        onChange={(e) => setAgreed(e.target.checked)}
      />

      <div className="mt-4">
        <SubmitButton
          label="Продолжи"
          disabled={!agreed}
          disabledTooltip="Прифати ги условите за да продолжиш"
        />
      </div>
    </form>
  );
}
