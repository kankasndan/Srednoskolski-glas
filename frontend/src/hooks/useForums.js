"use client";

import { useEffect, useState } from "react";
import { getForums } from "@/api/forums";

// Loads forums via getForums() and exposes loading/error so callers never
// assume the data is present on first render.
export function useForums() {
  const [forums, setForums] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let active = true;

    getForums()
      .then((data) => {
        if (active) setForums(data);
      })
      .catch((err) => {
        if (active) setError(err);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, []);

  return { forums, loading, error };
}
