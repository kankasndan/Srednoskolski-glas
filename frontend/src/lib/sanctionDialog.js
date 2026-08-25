const listeners = new Set();

/** Re-open the sanction popup (e.g. after clicking create-thread while banned). */
export function requestSanctionDialog(notice) {
  for (const listener of listeners) {
    listener(notice);
  }
}

export function subscribeSanctionDialog(listener) {
  listeners.add(listener);
  return () => listeners.delete(listener);
}
