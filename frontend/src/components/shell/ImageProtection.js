"use client";

import { useEffect } from "react";

// Sprecuva vlecenje i desen klik vrz slikite na sajtot.
export default function ImageProtection() {
  useEffect(() => {
    function blockOnImages(event) {
      if (event.target.tagName === "IMG") {
        event.preventDefault();
      }
    }

    document.addEventListener("dragstart", blockOnImages);
    document.addEventListener("contextmenu", blockOnImages);

    return () => {
      document.removeEventListener("dragstart", blockOnImages);
      document.removeEventListener("contextmenu", blockOnImages);
    };
  }, []);

  return null;
}
