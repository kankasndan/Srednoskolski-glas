"use client";

import { createContext, useContext, useEffect, useState } from "react";

// Kolku nivoa odgovori se sobiraat vo shirinata na sekoj ekran. Koga kje se
// otvori nivo poveke, razgovorot se pomestuva levo — najgornite nivoa delat
// kolona so roditelot, a najnovata granka ostanuva chitliva.
const VISIBLE_LEVELS = { phone: 4, tablet: 8, desktop: 12 };

const DepthContext = createContext(null);

export function CommentShiftProvider({ children }) {
  const [openLevels, setOpenLevels] = useState({});
  const deepest = Object.entries(openLevels).reduce(
    (max, [depth, count]) => (count > 0 ? Math.max(max, Number(depth)) : max),
    0,
  );

  return (
    <DepthContext.Provider value={{ deepest, setOpenLevels }}>
      {children}
    </DepthContext.Provider>
  );
}

// Od koj ekran natamu nivoto ispagja od prozorecot: "phone" znachi samo na
// telefon, "tablet" na telefon i tablet, "all" nasekade, null nikade.
function outOfWindow(depth, deepest) {
  if (depth < 0) return null;
  if (depth <= deepest - VISIBLE_LEVELS.desktop) return "all";
  if (depth <= deepest - VISIBLE_LEVELS.tablet) return "tablet";
  if (depth <= deepest - VISIBLE_LEVELS.phone) return "phone";
  return null;
}

// Komentarot javuva dali ima otvoreni odgovori, a nazad dobiva dali negovite
// deca se vlechat vo negovata kolona (merged) i dali toj samiot e vlechen vo
// kolonata na roditelot (pulled).
export function useCommentShift(depth, open) {
  const { deepest, setOpenLevels } = useContext(DepthContext);
  const level = depth + 1;

  useEffect(() => {
    if (!open) return;

    setOpenLevels((prev) => ({ ...prev, [level]: (prev[level] ?? 0) + 1 }));
    return () => setOpenLevels((prev) => ({ ...prev, [level]: prev[level] - 1 }));
  }, [level, open, setOpenLevels]);

  return {
    merged: outOfWindow(depth, deepest),
    pulled: outOfWindow(depth - 1, deepest),
  };
}
