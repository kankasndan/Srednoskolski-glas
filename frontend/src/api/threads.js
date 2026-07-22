const USE_MOCK = true;

const MOCK_THREADS = {
  "drzhavna_matura/1": "/MOCK_JSON/thread-drzhavna-matura-mock.json",
  "opshti_diskusii/101": "/MOCK_JSON/thread-opshti-diskusii-mock.json",
  "opshti_diskusii/102": "/MOCK_JSON/thread-opshti-diskusii-102-mock.json",
};

export async function getThread(forumSlug, threadId) {
  const url = USE_MOCK
    ? MOCK_THREADS[`${forumSlug}/${threadId}`]
    : `/api/forums/${forumSlug}/threads/${threadId}`; // CHECK da li e ovaa tocnata ruta

  if (!url) return null;

  const res = await fetch(url);

  if (res.status === 404) return null;
  if (!res.ok) throw new Error(`Failed to load thread: ${res.status}`);

  const { data } = await res.json();

  return data;
}
