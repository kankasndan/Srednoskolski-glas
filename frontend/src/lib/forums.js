// Simple Macedonian Cyrillic -> ASCII slug, matching the naming convention
// e.g. "Вештачка интелигенција" -> "veshtachka_inteligencija"
const CYRILLIC_TO_LATIN = {
  а: "a", б: "b", в: "v", г: "g", д: "d", ѓ: "gj", е: "e", ж: "zh", з: "z",
  ѕ: "dz", и: "i", ј: "j", к: "k", л: "l", љ: "lj", м: "m", н: "n", њ: "nj",
  о: "o", п: "p", р: "r", с: "s", т: "t", ќ: "kj", у: "u", ф: "f", х: "h",
  ц: "c", ч: "ch", џ: "dzh", ш: "sh",
};

export function slugify(name) {
  return name
    .toLowerCase()
    .split("")
    .map((char) => (char === " " ? "_" : CYRILLIC_TO_LATIN[char] ?? char))
    .join("");
}

export function createSchoolForum(name) {
  return {
    name,
    icon: "/icons/uchilishte.svg",
    slug: slugify(name),
  };
}
