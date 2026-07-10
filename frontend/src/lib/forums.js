// Simple Macedonian Cyrillic -> ASCII slug, matching the naming convention
// e.g. "Вештачка интелегенција" -> "vestacka_intelegencija"
const CYRILLIC_TO_LATIN = {
  а: "a", б: "b", в: "v", г: "g", д: "d", ѓ: "g", е: "e", ж: "z", з: "z",
  ѕ: "z", и: "i", ј: "j", к: "k", л: "l", љ: "l", м: "m", н: "n", њ: "n",
  о: "o", п: "p", р: "r", с: "s", т: "t", ќ: "k", у: "u", ф: "f", х: "h",
  ц: "c", ч: "c", џ: "d", ш: "s",
};

export function slugify(name) {
  return name
    .toLowerCase()
    .split("")
    .map((char) => (char === " " ? "_" : CYRILLIC_TO_LATIN[char] ?? char))
    .join("");
}

// Add / edit forum names here. The icon (public/<slug>.svg) and the link
// (/p/<slug>) are derived automatically from each name.
const FORUM_NAMES = [
  "Општи дискусии",
  "Државна матура",
  "Помош при учење",
  "Вештачка интелегенција",
  "Факултети",
  "Странски јазици",
  "Кариера и професии",
  "Студии во странство",
  "Ментално здравје",
  "Воннаставни активности",
  "Технологија и програмирање",
  "Забава и култура",
  "Спорт",
  "Социјални прашања",
  "Претстави се",
  "Слободни дискусии",
];

export const FORUMS = FORUM_NAMES.map((name) => ({
  name,
  slug: slugify(name),
}));
