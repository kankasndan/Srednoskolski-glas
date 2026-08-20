"use client";

import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCheck, faLock } from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;
import { getCities, updateProfile } from "@/api/profile";
import { uploadMedia } from "@/api/media";
import { CITIES } from "@/lib/schools";
import InfoDialog from "@/components/ui/InfoDialog";
import PrimaryButton from "@/components/ui/PrimaryButton";
import SelectField from "@/components/ui/SelectField";
import { userFacingError } from "@/lib/api";

const SAVED_TITLE = "Промените на профилот се зачувани.";

// Mora da odgovara na `avatars.defaults` vo backendot.
const DEFAULT_AVATARS = [
  "/avatars/default-1.svg",
  "/avatars/default-2.svg",
  "/avatars/default-3.svg",
  "/avatars/default-4.svg",
];

// Vo izborot na korisnikot gi ima i novite; avtomatskiot izbor ne gi koristi.
const SELECTABLE_AVATARS = [
  ...DEFAULT_AVATARS,
  "/avatars/buv.svg",
  "/avatars/lisica.svg",
  "/avatars/slon.svg",
  "/avatars/zirafa.svg",
];

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

const YEARS = ["1", "2", "3", "4"];

function yearOptionsFromUser(user) {
  const min = user?.capabilities?.min_grade;
  const max = user?.capabilities?.max_grade;
  if (min == null || max == null) return YEARS;

  const options = [];
  for (let grade = Number(min); grade <= Number(max); grade += 1) {
    options.push(String(grade));
  }

  return options.length > 0 ? options : YEARS;
}

function schoolLockedMessage(user) {
  if (user?.capabilities?.can_change_school !== false) return null;

  const unlocksAt = user.capabilities?.school_change_unlocks_at;
  const year = unlocksAt ? Number(String(unlocksAt).slice(0, 4)) : null;

  return year
    ? `Не можеш да го промениш училиштето до септември ${year}.`
    : "Не можеш да го промениш училиштето до следен септември.";
}

function schoolValueFromUser(user) {
  const school = user?.student_data?.school ?? user?.studentData?.school;
  const city = school?.city?.name ?? school?.city;
  if (!school?.name || !city) return "";
  return `${school.name}|${city}`;
}

function vocationFromUser(user) {
  return user?.student_data?.vocation?.name ?? user?.studentData?.vocation?.name ?? "";
}

function gradeFromUser(user) {
  const grade = user?.student_data?.grade ?? user?.studentData?.grade;
  return grade != null ? String(grade) : "";
}

function citiesToGroups(cities) {
  return cities.map((city) => ({
    city: city.name,
    schools: (city.schools ?? []).map((school) => school.name ?? school),
  }));
}

export default function EditProfileForm({ user: initialUser }) {
  const router = useRouter();
  const fileInputRef = useRef(null);
  const [imageUrl, setImageUrl] = useState(initialUser.imageUrl || DEFAULT_AVATARS[0]);
  const [pendingFile, setPendingFile] = useState(null);
  const [previewUrl, setPreviewUrl] = useState(initialUser.imageUrl || DEFAULT_AVATARS[0]);
  const [school, setSchool] = useState(schoolValueFromUser(initialUser));
  const [area, setArea] = useState(vocationFromUser(initialUser));
  const [year, setYear] = useState(gradeFromUser(initialUser));
  const [schoolGroups, setSchoolGroups] = useState(CITIES);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState("");
  const canChangeSchool = initialUser.capabilities?.can_change_school !== false;
  const schoolLockMessage = schoolLockedMessage(initialUser);
  const yearOptions = yearOptionsFromUser(initialUser);

  useEffect(() => {
    let active = true;

    getCities()
      .then((cities) => {
        if (active && cities.length > 0) {
          setSchoolGroups(citiesToGroups(cities));
        }
      })
      .catch(() => {});

    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    return () => {
      if (previewUrl?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl);
      }
    };
  }, [previewUrl]);

  function selectDefaultAvatar(src) {
    if (previewUrl?.startsWith("blob:")) {
      URL.revokeObjectURL(previewUrl);
    }
    setPendingFile(null);
    setImageUrl(src);
    setPreviewUrl(src);
  }

  function handleRemoveAvatar() {
    selectDefaultAvatar(DEFAULT_AVATARS[0]);
  }

  function handleFileChange(event) {
    const file = event.target.files?.[0];
    event.target.value = "";
    if (!file || !file.type.startsWith("image/")) return;

    if (previewUrl?.startsWith("blob:")) {
      URL.revokeObjectURL(previewUrl);
    }

    setPendingFile(file);
    setPreviewUrl(URL.createObjectURL(file));
    setImageUrl("");
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSaving(true);
    setError("");

    try {
      let nextImageUrl = imageUrl;

      if (pendingFile) {
        const uploaded = await uploadMedia(pendingFile, "avatars");
        nextImageUrl = uploaded.url;
      }

      const payload = {
        image_url: nextImageUrl,
      };

      if (school && area && year) {
        payload.school = school;
        payload.area = area;
        payload.year = year;
      }

      await updateProfile(payload);
      // Vrakjanjeto na profilot cheka da se zatvori potvrdata.
      setSaved(true);
    } catch (err) {
      const validation = err.body?.errors;
      if (validation) {
        setError(Object.values(validation).flat().join(" "));
      } else {
        setError(userFacingError(err, "Неуспешно зачувување. Обиди се повторно."));
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="flex w-full flex-col gap-6">
      {/* Na telefon naslovot e vo lenta so linija pod nego. */}
      <section className="flex flex-col rounded-3xl border border-[#CFE9ED] bg-white md:border-[#E5E5E5]">
        <h2 className="border-b border-[#CFE9ED] px-6 py-5 font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-black md:border-b-0 md:px-8 md:pb-0 md:pt-8 md:text-[20px]">
          Профил
        </h2>

        <div className="flex flex-col gap-7 p-6 md:gap-8 md:p-8">
          <div className="flex flex-col gap-4">
            <p className="font-[family-name:var(--font-manrope)] text-[14px] font-bold text-[#595959] md:text-black">
              Слика на профилот
            </p>

            <div className="flex flex-wrap items-center gap-5 md:gap-4">
              <img
                src={previewUrl}
                alt=""
                width={96}
                height={96}
                className="size-18 shrink-0 rounded-full object-cover md:size-24"
              />

              <div className="flex flex-wrap items-center gap-2 md:gap-3">
                <PrimaryButton
                  type="button"
                  onClick={() => fileInputRef.current?.click()}
                  className="flex h-10 w-24 items-center justify-center font-[family-name:var(--font-manrope)] text-[14px] md:w-auto md:px-4"
                >
                  <span className="md:hidden">Прикачи</span>
                  <span className="hidden md:inline">Прикачи слика</span>
                </PrimaryButton>
                <button
                  type="button"
                  onClick={handleRemoveAvatar}
                  className="flex h-10 w-24 cursor-pointer items-center justify-center rounded-xl border border-[#582FF5] bg-white font-[family-name:var(--font-manrope)] text-[14px] font-bold text-[#582FF5] transition-colors active:bg-[#F1EEFE] hover:bg-[#F1EEFE] md:w-auto md:px-4"
                >
                  Отстрани
                </button>
              </div>
            </div>

            <input
              ref={fileInputRef}
              type="file"
              accept="image/jpeg,image/png,image/webp,image/gif"
              className="hidden"
              onChange={handleFileChange}
            />

            <p className="font-[family-name:var(--font-manrope)] text-[13px] leading-[19.5px] text-[#595959] md:text-[14px]">
              Или избери стандарден аватар:
            </p>

            <div className="flex flex-wrap items-center gap-3">
              {SELECTABLE_AVATARS.map((src) => {
                const selected = !pendingFile && imageUrl === src;

                return (
                  <button
                    key={src}
                    type="button"
                    onClick={() => selectDefaultAvatar(src)}
                    aria-label="Избери стандарден аватар"
                    aria-pressed={selected}
                    className="relative size-10 cursor-pointer rounded-full md:size-14"
                  >
                    <img
                      src={src}
                      alt=""
                      className="size-10 rounded-full object-cover md:size-14"
                    />
                    {selected ? (
                      <span className="absolute -bottom-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full border border-black bg-white text-black md:-top-0.5 md:bottom-auto md:size-5 md:border-0 md:bg-[#582FF5] md:text-white">
                        <FontAwesomeIcon icon={faCheck} className="text-[8px] md:text-[10px]" />
                      </span>
                    ) : null}
                  </button>
                );
              })}
            </div>
          </div>

          <div className="flex flex-col gap-2 border-t border-[#E5E5E5] pt-7 md:border-t-0 md:pt-0">
            <div className="flex flex-wrap items-center gap-2">
              <label
                htmlFor="pseudonym"
                className="font-[family-name:var(--font-manrope)] text-[14px] font-bold text-[#595959] md:text-black"
              >
                Псевдоним
              </label>
              <span className="flex items-center gap-1.5 rounded-md bg-[#F5F5F5] px-2 py-1 font-[family-name:var(--font-manrope)] text-[12px] leading-none text-[#595959]">
                <FontAwesomeIcon icon={faLock} className="text-[10px]" />
                Не може да се промени
              </span>
            </div>
            <input
              id="pseudonym"
              type="text"
              readOnly
              value={initialUser.username ?? ""}
              className="h-10 w-full cursor-not-allowed rounded-xl border border-[#CCCCCC] bg-[#F5F5F5] px-4 font-[family-name:var(--font-manrope)] text-[14px] text-[#595959] md:h-12"
            />
            <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-5 text-[#595959]">
              Псевдонимот е постојан и не може да биде изменет по регистрацијата.
            </p>
          </div>
        </div>
      </section>

      <section className="flex flex-col rounded-3xl border border-[#CFE9ED] bg-white md:border-[#E5E5E5]">
        <h2 className="border-b border-[#CFE9ED] px-6 py-5 font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-black md:border-b-0 md:px-8 md:pb-0 md:pt-8 md:text-[20px]">
          Информации за училиште
        </h2>

        <div className="flex flex-col gap-6 p-6 md:p-8">
          <div className="flex flex-col gap-2">
            {!canChangeSchool ? (
              <span className="flex w-fit items-center gap-1.5 rounded-md bg-[#F5F5F5] px-2 py-1 font-[family-name:var(--font-manrope)] text-[12px] leading-none text-[#595959]">
                <FontAwesomeIcon icon={faLock} className="text-[10px]" />
                Заклучено до септември
              </span>
            ) : null}
            <SelectField
              compact
              id="school"
              label="Училиште"
              value={school}
              onChange={setSchool}
              placeholder="Избери училиште"
              groups={schoolGroups}
              disabled={!canChangeSchool}
            />
            {schoolLockMessage ? (
              <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-5 text-[#595959]">
                {schoolLockMessage}
              </p>
            ) : null}
          </div>
          <SelectField
            compact
            id="area"
            label="Подрачје на образование"
            value={area}
            onChange={setArea}
            placeholder="Избери подрачје"
            options={area && !AREAS.includes(area) ? [area, ...AREAS] : AREAS}
          />
          <SelectField
            compact
            id="year"
            label="Година"
            value={year}
            onChange={setYear}
            placeholder="Избери година"
            options={yearOptions}
          />
        </div>
      </section>

      {error ? (
        <p className="font-[family-name:var(--font-manrope)] text-[13px] text-[var(--color-error)]">
          {error}
        </p>
      ) : null}

      <div className="flex flex-wrap items-center justify-end gap-2 md:gap-3">
        <button
          type="button"
          onClick={() => router.push("/profile")}
          className="flex h-10 w-36 cursor-pointer items-center justify-center rounded-xl border border-[#582FF5] bg-white font-[family-name:var(--font-manrope)] text-[14px] font-bold text-black transition-colors active:bg-[#F1EEFE] hover:bg-[#F1EEFE] md:w-auto md:px-5"
        >
          Откажи
        </button>
        <PrimaryButton
          type="submit"
          disabled={saving}
          className="flex h-10 w-36 items-center justify-center font-[family-name:var(--font-manrope)] text-[14px] disabled:opacity-60 md:w-auto md:px-5"
        >
          {saving ? "Се зачувува…" : "Зачувај промени"}
        </PrimaryButton>
      </div>

      <InfoDialog
        open={saved}
        title={SAVED_TITLE}
        onClose={() => window.location.assign("/profile")}
      />
    </form>
  );
}
