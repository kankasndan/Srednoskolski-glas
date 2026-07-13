// Tracks whether the user has navigated *within* the app during this page load.
// The module re-initializes on every full page load, so a direct visit or an
// external link starts as `false`. Once any in-app navigation happens it becomes
// `true`. Auth pages use this to decide whether the back button can safely go
// back (in-app) or should fall back to /feed (arrived directly).
let inApp = false;

export function markInAppNavigation() {
  inApp = true;
}

export function hasNavigatedInApp() {
  return inApp;
}
