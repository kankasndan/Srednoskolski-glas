/**
 * Compact display for engagement counts.
 * < 1_000      → 999
 * < 10_000     → 1.3k
 * < 1_000_000  → 23k
 * < 10_000_000 → 1.3m
 * >= 10_000_000 → 12m
 */
export function formatCount(value) {
  const count = Number(value);

  if (!Number.isFinite(count) || count < 0) {
    return "0";
  }

  if (count < 1000) {
    return String(Math.floor(count));
  }

  if (count < 10_000) {
    return `${trimTrailingZero(Math.floor(count / 100) / 10)}k`;
  }

  if (count < 1_000_000) {
    return `${Math.floor(count / 1000)}k`;
  }

  if (count < 10_000_000) {
    return `${trimTrailingZero(Math.floor(count / 100_000) / 10)}m`;
  }

  return `${Math.floor(count / 1_000_000)}m`;
}

function trimTrailingZero(value) {
  return Number.isInteger(value) ? String(value) : value.toFixed(1).replace(/\.0$/, "");
}
