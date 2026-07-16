const USE_MOCK = true;

export async function getForums() {
  const url = USE_MOCK ? "/forums-mock.json" : "/api/forums";
  const res = await fetch(url);
  if (!res.ok) throw new Error(`Failed to load forums: ${res.status}`);
  const json = await res.json();
  return json.data;
}
