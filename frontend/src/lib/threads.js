/**
 * Shared thread data used by FeedThreads, ForumThreadList, and thread detail pages.
 */

export const FEED_THREADS = [
  {
    tags: [
      { label: "Државна матура", icon: "/icons/drzavna_matura.svg", iconZoom: true },
      { label: "марко_2026", icon: "/Generic avatar.svg" },
      { label: "Гим. Орце Николов", icon: "/icons/uciliste.svg" },
    ],
    title: "Кои се најдобрите ресурси за подготовка на матура по математика?",
    excerpt: "Здраво на сите. Секој совет е добредојден...",
    postedAgo: "пред 2ч.",
    views: 56,
    votes: 24,
    comments: 8,
  },
  {
    tags: [
      { label: "Препорачано", tone: "highlight" },
      { label: "Факултети", icon: "/icons/fakulteti.svg", iconZoom: true },
      { label: "елена.к", icon: "/Generic avatar.svg" },
      { label: "СУГС Михајло Пупин", icon: "/icons/uciliste.svg" },
    ],
    title: "Brainster Next vs ЕТФ - кој е подобар за софтверско инженерство?",
    excerpt: "Размислувам помеѓу овие два факултета и ме интересира мислење на постари ученици...",
    postedAgo: "пред 4ч.",
    views: 156,
    votes: 18,
    comments: 11,
  },
  {
    tags: [
      { label: "Ментално здравје", icon: "/icons/mentalno_zdravje.svg", iconZoom: true },
      { label: "анонимен_111", icon: "/Generic avatar.svg" },
      { label: "Гим. Никола Карев", icon: "/icons/uciliste.svg" },
    ],
    title: "Како се справувате со стрес пред испити?",
    excerpt: "Имам матура за два месеца и не можам да спијам нормално...",
    postedAgo: "пред 1д.",
    views: 203,
    votes: 31,
    comments: 14,
  },
  {
    tags: [
      { label: "СУГС Михајло Пупин", icon: "/icons/uciliste.svg" },
      { label: "стефан_22", icon: "/Generic avatar.svg" },
    ],
    title: "Кога ќе се одржи екскурзијата за матуранти?",
    excerpt: "Дали некој има информација...",
    postedAgo: "пред 3д.",
    views: 42,
    votes: 9,
    comments: 5,
  },
];

export const FEED_THREAD_LIST = Array.from({ length: 16 }, (_, index) => {
  const base = FEED_THREADS[index % FEED_THREADS.length];
  let ageInDays = 0;
  let postedAgo = base.postedAgo;

  if (index % 5 === 0) {
    ageInDays = 0;
    postedAgo = `пред ${2 + (index % 3)}ч.`;
  } else if (index % 5 === 1) {
    ageInDays = 1 + (index % 4);
    postedAgo = `пред ${ageInDays}д.`;
  } else if (index % 5 === 2) {
    ageInDays = 8 + (index % 10);
    postedAgo = `пред ${ageInDays}д.`;
  } else if (index % 5 === 3) {
    ageInDays = 25 + index;
    postedAgo = `пред 1м.`;
  } else {
    ageInDays = 90 + index * 5;
    postedAgo = `пред ${Math.floor(ageInDays / 30)}м.`;
  }

  return {
    ...base,
    id: index,
    postedAgo,
    ageInDays,
    votes: base.votes + (index * 3) % 17,
    comments: base.comments + (index * 2) % 9,
    views: base.views + (index * 15) % 80,
    image: index === 2 || index === 6 ? "/thread-placeholder.png" : null,
  };
});

export const FORUM_THREADS = [
  {
    id: 1,
    tags: [
      { label: "марко_2026", icon: "/Generic avatar.svg" },
      { label: "Гим. Орце Николов", icon: "/icons/uciliste.svg" },
    ],
    title: "Кои се најдобрите ресурси за подготовка на матура по математика?",
    excerpt: "Здраво. Барам совети за најдобри книги, видеа и онлајн материјали...",
    postedAgo: "пред 2ч.",
    views: 56,
    comments: 42,
    votes: 87,
  },
  {
    id: 2,
    tags: [
      { label: "гоце_2027", icon: "/Generic avatar.svg" },
      { label: "Гим. Никола Карев", icon: "/icons/uciliste.svg" },
    ],
    title: "Дали матура по странски јазик е тешка ако имам B2?",
    excerpt: "Имам Cambridge B2 сертификат и размислувам да земам англиски на матура...",
    postedAgo: "пред 3д.",
    views: 134,
    comments: 15,
    votes: 28,
    image: "/thread-placeholder.png",
  },
  {
    id: 3,
    tags: [
      { label: "Истакнато", tone: "highlight" },
      { label: "ана_матуранка", icon: "/Generic avatar.svg" },
      { label: "СУГС Михајло Пупин", icon: "/icons/uciliste.svg" },
    ],
    title: "[GUIDE] Како се справив со матурата по македонски - мојот метод",
    excerpt: "Завршив матура минатата година со 4.95. Сакам да го споделам начинот на кој...",
    postedAgo: "пред 5д.",
    views: 487,
    comments: 89,
    votes: 312,
  },
];
