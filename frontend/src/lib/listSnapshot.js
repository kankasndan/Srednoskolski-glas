const snapshots = new Map();

export function listSnapshotKey({ forum, searchQuery, listPath }) {
  return JSON.stringify({
    forum: forum ?? null,
    q: searchQuery ?? null,
    listPath: listPath ?? null,
  });
}

export function readListSnapshot(key) {
  const snapshot = snapshots.get(key);
  if (!snapshot?.hasLoaded) {
    return null;
  }

  return snapshot;
}

export function writeListSnapshot(key, snapshot) {
  if (!snapshot?.hasLoaded) {
    return;
  }

  snapshots.set(key, snapshot);
}

export function isReloadNavigation() {
  if (typeof performance === "undefined") {
    return false;
  }

  const entry = performance.getEntriesByType?.("navigation")?.[0];
  if (entry) {
    return entry.type === "reload";
  }

  return performance.navigation?.type === 1;
}
