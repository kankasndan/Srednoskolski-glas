"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { apiFetch } from "@/lib/api";
import { CITIES } from "@/lib/schools";
import { loadSessionUser, setSessionUser } from "@/lib/sessionUser";
import TextField from "@/components/ui/TextField";
import SelectField from "@/components/ui/SelectField";
import TermsCheckbox from "@/components/auth/TermsCheckbox";
import Checkbox from "@/components/ui/Checkbox";
import SubmitButton from "@/components/ui/SubmitButton";

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

function formatApiError(data) {
  if (data?.errors) {
    const firstField = Object.keys(data.errors)[0];
    return data.errors[firstField]?.[0] || "Провери ги внесените податоци.";
  }

  return data?.message || "Неуспешно зачувување. Обиди се повторно.";
}

const USERNAME_MIN_LENGTH = 3;

export default function OnboardingForm() {
  const router = useRouter();
  const [username, setUsername] = useState("");
  const [usernameError, setUsernameError] = useState("");
  const [school, setSchool] = useState("");
  const [area, setArea] = useState("");
  const [year, setYear] = useState("");
  const [agreed, setAgreed] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const [notStudent, setNotStudent] = useState(false);

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
    else {
      setSchool("");
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");

    const trimmedUsername = username.trim();

    if (trimmedUsername.length < USERNAME_MIN_LENGTH) {
      setUsernameError(
        trimmedUsername
          ? `Псевдонимот мора да има најмалку ${USERNAME_MIN_LENGTH} карактери.`
          : "Внеси псевдоним.",
      );
      return;
    }

    setUsernameError("");

    const payload = {
      username: trimmedUsername,
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

      const nextUser = data.user ?? null;
      if (nextUser && typeof nextUser === "object") {
        nextUser.capabilities = data.capabilities ?? nextUser.capabilities ?? null;
        nextUser.permissions = data.permissions ?? nextUser.permissions ?? [];
      }

      if (nextUser) {
        setSessionUser(nextUser);
      }
      await loadSessionUser({ force: true });
      router.push("/register/onboarding_2");
    } catch {
      setError("Не можеме да се поврземе со серверот. Обиди се повторно.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form
      onSubmit={handleSubmit}
      className="mx-auto mt-[43px] flex w-full max-w-[342px] flex-col gap-3 sm:max-w-[380px] md:max-w-[400px] lg:mt-12 2xl:max-w-[440px] 2xl:gap-4"
    >
      <TextField
        id="pseudonym"
        label="Псевдоним (3-20 карактери)"
        required
        placeholder="пр. марко_2026"
        maxLength={20}
        value={username}
        onChange={(event) => {
          setUsername(event.target.value);
          if (usernameError) setUsernameError("");
        }}
        error={usernameError}
      />

      <div className="flex flex-col gap-0">
        <Checkbox
          checked={notStudent}
          onChange={(event) => handleNotStudentChange(event.target.checked)}
          className="mb-2"
        >
          <span className="font-(family-name:--font-manrope) text-[12px] font-normal leading-[19.4px] text-[#000000] 2xl:text-[14px]">
            Не сум средношколец
          </span>
        </Checkbox>

        <p className="-mt-1 mb-3 font-(family-name:--font-manrope) text-[12px] text-[#595959] 2xl:text-[14px]">
          Доколку не си средношколец, можеш да ја користиш платформата само за
          читање и коментирање на дискусии.
        </p>
      </div>

      <SelectField
        id="school"
        label="Училиште"
        required
        value={school}
        onChange={setSchool}
        placeholder="Избери училиште"
        groups={schoolGroups}
        disabled={notStudent}
      />

      <SelectField
        id="area"
        label="Подрачје на образование"
        required
        value={area}
        onChange={setArea}
        placeholder="Избери струка"
        options={AREAS}
        disabled={notStudent}
      />

      <SelectField
        id="year"
        label="Година"
        required
        value={year}
        onChange={setYear}
        placeholder="Избери година"
        options={YEARS}
        disabled={notStudent}
      />

      <div className="mt-4 lg:mt-0">
        <TermsCheckbox
          checked={agreed}
          onChange={(e) => setAgreed(e.target.checked)}
        />
      </div>

      {error && (
        <p className="font-(family-name:--font-manrope) text-[13px] text-red-600">
          {error}
        </p>
      )}

      <div className="mt-10 lg:mt-4">
        <SubmitButton
          label={submitting ? "Зачувување..." : "Продолжи"}
          disabled={!username.trim() || !agreed || submitting || (!notStudent && (!school || !area))}
          disabledTooltip="Прифати ги условите за да продолжиш"
        />
      </div>
    </form>
  );
}
