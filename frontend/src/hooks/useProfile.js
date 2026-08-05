"use client";

import { useEffect, useState } from "react";
import { getProfileUser } from "@/api/profile";

export function useProfile() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let active = true;

    getProfileUser()
      .then((data) => {
        if (active) setUser(data);
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

  return { user, loading, error };
}
