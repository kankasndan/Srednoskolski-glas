"use client";

import { useEffect, useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faXmark } from "@fortawesome/free-solid-svg-icons";
import { searchGifs } from "@/api/giphy";

config.autoAddCss = false;

const DEBOUNCE_MS = 300;

export default function GifPicker({ onSelect, onClose }) {
  const [query, setQuery] = useState("");
  const [gifs, setGifs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  // Pochekaj da prestane kucanjeto pred da se prashuva Giphy.
  useEffect(() => {
    let active = true;

    const timer = setTimeout(() => {
      setLoading(true);
      searchGifs(query)
        .then((results) => {
          if (!active) return;
          setGifs(results);
          setError("");
        })
        .catch(() => {
          if (active) setError("Не успеа вчитувањето на GIF-овите.");
        })
        .finally(() => {
          if (active) setLoading(false);
        });
    }, DEBOUNCE_MS);

    return () => {
      active = false;
      clearTimeout(timer);
    };
  }, [query]);

  return (
    <div className="flex flex-col gap-3 rounded-2xl border border-[#CCCCCC] bg-white p-4">
      <div className="flex items-center justify-between">
        <span className="font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
          Powered by GIPHY
        </span>
        <button
          type="button"
          aria-label="Затвори ги GIF-овите"
          onClick={onClose}
          className="flex size-6 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>
      </div>

      <input
        autoFocus
        type="text"
        value={query}
        onChange={(event) => setQuery(event.target.value)}
        placeholder="Пребарај GIF..."
        className="h-10 w-full min-w-0 rounded-xl border border-[#CCCCCC] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] leading-5 text-black transition-colors placeholder:text-[#595959] focus:border-[#582FF5] focus:outline-none"
      />

      {error ? (
        <p className="text-[13px] text-[var(--color-error)]">{error}</p>
      ) : gifs.length === 0 ? (
        <p className="text-[13px] text-[#595959]">
          {loading ? "Се вчитуваат GIF-ови…" : "Нема резултати."}
        </p>
      ) : null}

      <div className="max-h-72 overflow-y-auto">
        <div className="columns-[8rem] gap-3">
          {gifs.map((gif) => (
            <button
              key={gif.id}
              type="button"
              onClick={() => onSelect(gif)}
              className="mb-3 block w-full cursor-pointer break-inside-avoid overflow-hidden rounded-xl bg-[#F5F5F5] transition-opacity hover:opacity-80"
            >
              <img src={gif.url} alt={gif.title} className="w-full" />
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
