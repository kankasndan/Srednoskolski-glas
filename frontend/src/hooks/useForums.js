"use client";

import { useEffect, useState } from "react";
import { getForums } from "@/api/forums";

// Loads forums via getForums() and exposes the general + school-by-city lists
// with loading/error so callers never assume the data is present on first render.
export function useForums() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let active = true;

    getForums()
      .then((payload) => {
        if (active) setData(payload);
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

  return {
    general: data?.general ?? [],
    schoolsByCity: data?.schools_by_city ?? [],
    loading,
    error,
  };
}
