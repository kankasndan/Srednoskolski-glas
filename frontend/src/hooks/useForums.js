"use client";

import { useEffect, useState } from "react";
import { getForums } from "@/api/forums";

let cachedForums = null;
/** @type {Promise<object> | null} */
let inflight = null;

function loadForums() {
  if (cachedForums) return Promise.resolve(cachedForums);
  if (inflight) return inflight;

  inflight = getForums()
    .then((payload) => {
      cachedForums = payload;
      return payload;
    })
    .finally(() => {
      inflight = null;
    });

  return inflight;
}

export function invalidateForumsCache() {
  cachedForums = null;
  inflight = null;
}

// Loads forums via getForums() and exposes the general + school-by-city lists
// with loading/error so callers never assume the data is present on first render.
export function useForums() {
  const [data, setData] = useState(cachedForums);
  const [loading, setLoading] = useState(!cachedForums);
  const [error, setError] = useState(null);

  useEffect(() => {
    let active = true;

    loadForums()
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
