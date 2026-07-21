"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { apiFetch } from "@/lib/api";
import { CITIES } from "@/lib/schools";
import TextField from "@/components/TextField";
import SelectField from "@/components/SelectField";
import SchoolSelect from "@/components/SchoolSelect";
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

function formatApiError(data) {
  if (data?.errors) {
    const firstField = Object.keys(data.errors)[0];
    return data.errors[firstField]?.[0] || "Провери ги внесените податоци.";
  }

  return data?.message || "Неуспешно зачувување. Обиди се повторно.";
}

export default function OnboardingForm() {
  const router = useRouter();
  const [school, setSchool] = useState("");
  const [area, setArea] = useState("");
  const [year, setYear] = useState("");
  const [notStudent, setNotStudent] = useState(false);
  const [agreed, setAgreed] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const schoolGroups = [...CITIES].sort(
    (a, b) => b.schools.length - a.schools.length,
  );

  function handleNotStudentChange(checked) {
    setNotStudent(checked);
    if (checked) {
      setSchool("");
      setArea("");
      setYear("");
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");

    const formData = new FormData(e.currentTarget);
    const username = String(formData.get("pseudonym") || "").trim();

    const payload = {
      username,
      is_student: !notStudent,
    };

    if (!notStudent) {
      payload.school = school;
      payload.area = area;
      payload.year = year;
    }

    setSubmitting(true);

    try {
      const response = await apiFetch("/api/onboarding", {
        method: "PUT",
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        setError(formatApiError(data));
        return;
      }

      router.push("/register/onboarding_2");
    } catch {
      setError("Не можеме да се поврземе со серверот. Обиди се повторно.");
    } finally {
      setSubmitting(false);
    }
  }

  useEffect(() => {
    console.log(schoolGroups);
  }, []);

  return (
    <form
      onSubmit={handleSubmit}
      className="mx-auto mt-12 flex w-full max-w-100 flex-col gap-3"
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
          onChange={(e) => handleNotStudentChange(e.target.checked)}
          className="h-4 w-4 shrink-0 accent-[#582FF5] 2xl:h-5 2xl:w-5"
        />
        <span className="font-(family-name:--font-manrope) text-[12px] font-normal leading-[19.4px] text-[#595959] 2xl:text-[14px]">
          Не сум средношколец
        </span>
      </label>

      <SchoolSelect
        id="school"
        label="Училиште"
        required={!notStudent}
        value={school}
        onChange={setSchool}
        placeholder="Избери училиште"
        groups={schoolGroups}
        notStudent={notStudent}
        onNotStudentChange={handleNotStudentChange}
        disabled={notStudent}
        tooltip={notStudent ? LOCKED_HINT : undefined}
      />

      <SelectField
        id="area"
        label="Подрачје"
        required={!notStudent}
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
        required={!notStudent}
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

      {error && (
        <p className="font-(family-name:--font-manrope) text-[13px] text-red-600">
          {error}
        </p>
      )}

      <div className="mt-4">
        <SubmitButton
          label={submitting ? "Зачувување..." : "Продолжи"}
          disabled={!agreed || submitting}
          disabledTooltip="Прифати ги условите за да продолжиш"
        />
      </div>
    </form>
  );
}
