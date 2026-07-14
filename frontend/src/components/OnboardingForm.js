"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
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

export default function OnboardingForm() {
  const router = useRouter();
  const [city, setCity] = useState("");
  const [school, setSchool] = useState("");
  const [area, setArea] = useState("");
  const [year, setYear] = useState("");
  const [agreed, setAgreed] = useState(false);

  const cityNames = CITIES.map((c) => c.city);
  const schools = CITIES.find((c) => c.city === city)?.schools ?? [];

  function handleCityChange(e) {
    setCity(e.target.value);
    setSchool(""); // reset school when the city changes
  }

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        // Onboarding done — drop the one-time pass so it can't be reopened.
        localStorage.removeItem("onboarding_pending");
        router.push("/feed");
      }}
      className="mx-auto mt-4 flex w-full max-w-[360px] flex-col gap-3 2xl:max-w-[440px] 2xl:gap-4"
    >
      <TextField
        id="pseudonym"
        label="Псевдоним (3-20 карактери)"
        required
        placeholder="пр. марко_2026"
        minLength={3}
        maxLength={20}
      />

      <SelectField
        id="city"
        label="Град"
        required
        value={city}
        onChange={handleCityChange}
        placeholder="Избери град"
        options={cityNames}
      />

      {city && (
        <SelectField
          id="school"
          label="Училиште"
          required
          value={school}
          onChange={(e) => setSchool(e.target.value)}
          placeholder="Избери училиште"
          options={schools}
        />
      )}

      <SelectField
        id="area"
        label="Подрачје"
        required
        value={area}
        onChange={(e) => setArea(e.target.value)}
        placeholder="Избери подрачје"
        options={AREAS}
      />

      <SelectField
        id="year"
        label="Година (опционално)"
        value={year}
        onChange={(e) => setYear(e.target.value)}
        placeholder="Избери година"
        options={YEARS}
      />

      <TermsCheckbox
        checked={agreed}
        onChange={(e) => setAgreed(e.target.checked)}
      />

      <SubmitButton
        label="Започни"
        disabled={!agreed}
        disabledTooltip="Прифати ги условите за да продолжиш"
      />
    </form>
  );
}
