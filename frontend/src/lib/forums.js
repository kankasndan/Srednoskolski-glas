// Simple Macedonian Cyrillic -> ASCII slug, matching the naming convention
// e.g. "Вештачка интелегенција" -> "vestacka_intelegencija"
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

// Add / edit forum names here. The link (/p/<slug>) is derived automatically
// from each name, while icon keeps the current public/icons filename.
const FORUM_DEFINITIONS = [
  { name: "Општи дискусии", icon: "/icons/opsti_diskusii.svg" },
  { name: "Државна матура", icon: "/icons/drzavna_matura.svg" },
  { name: "Помош при учење", icon: "/icons/pomos_pri_ucene.svg" },
  { name: "Вештачка интелегенција", icon: "/icons/vestacka_intelegencija.svg" },
  { name: "Факултети", icon: "/icons/fakulteti.svg" },
  { name: "Странски јазици", icon: "/icons/stranski_jazici.svg" },
  { name: "Кариера и професии", icon: "/icons/kariera_i_profesii.svg" },
  { name: "Студии во странство", icon: "/icons/studii_vo_stranstvo.svg" },
  { name: "Ментално здравје", icon: "/icons/mentalno_zdravje.svg" },
  { name: "Воннаставни активности", icon: "/icons/vonnastavni_aktivnosti.svg" },
  { name: "Технологија и програмирање", icon: "/icons/tehnologija_i_programirane.svg" },
  { name: "Забава и култура", icon: "/icons/zabava_i_kultura.svg" },
  { name: "Спорт", icon: "/icons/sport.svg" },
  { name: "Социјални прашања", icon: "/icons/socijalni_prasana.svg" },
  { name: "Претстави се", icon: "/icons/pretstavi_se.svg" },
  { name: "Слободни дискусии", icon: "/icons/slobodni_diskusii.svg" },
];

export const FORUMS = FORUM_DEFINITIONS.map((forum) => ({
  ...forum,
  slug: slugify(forum.name),
}));

export function createSchoolForum(name) {
  return {
    name,
    icon: "/icons/uciliste.svg",
    slug: slugify(name),
  };
}
