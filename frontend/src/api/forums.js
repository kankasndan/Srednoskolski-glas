const USE_MOCK = true; // Set to false to use the real API endpoint instead of the mock JSON file

export async function getForums() {
  const url = USE_MOCK ? "/forums-mock.json" : "/api/forums";
  const res = await fetch(url);
  if (!res.ok) throw new Error(`Failed to load forums: ${res.status}`);
  return res.json();
}
