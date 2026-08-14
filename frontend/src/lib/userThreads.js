import { slugify } from "@/lib/forums";

const STORAGE_KEY = "sg_user_threads";
const POLL_VOTES_KEY = "sg_poll_votes";
const VOTER_ID_KEY = "sg_voter_id";

export function getUserThreads() {
  if (typeof window === "undefined") return [];

  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    return stored ? JSON.parse(stored) : [];
  } catch {
    return [];
  }
}

function getVoterId() {
  if (typeof window === "undefined") return "server";

  let voterId = localStorage.getItem(VOTER_ID_KEY);
  if (!voterId) {
    voterId = `voter_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
    localStorage.setItem(VOTER_ID_KEY, voterId);
  }

  return voterId;
}

function getPollVotesMap() {
  if (typeof window === "undefined") return {};

  try {
    return JSON.parse(localStorage.getItem(POLL_VOTES_KEY) || "{}");
  } catch {
    return {};
  }
}

function savePollVotesMap(votesMap) {
  localStorage.setItem(POLL_VOTES_KEY, JSON.stringify(votesMap));
}

function buildDescription(body, attachments) {
  let description = body.trim();
  const extras = [];

  if (attachments.documentName) {
    extras.push(`Документ: ${attachments.documentName}`);
  }

  if (extras.length > 0) {
    description += `\n\n${extras.join("\n\n")}`;
  }

  return description;
}

export function getThreadBodyText(thread) {
  let text = thread?.fullBody || thread?.excerpt || "";

  if (thread?.videoLink) {
    text = text
      .replace(`\n\nВидео линк: ${thread.videoLink}`, "")
      .replace(`Видео линк: ${thread.videoLink}`, "")
      .trim();
  }

  return text;
}

function buildPollObject(pollQuestion, pollOptions) {
  const question = pollQuestion?.trim();
  const options = (pollOptions || []).map((option) => option.trim()).filter(Boolean);

  if (!question || options.length < 2) {
    return null;
  }

  return {
    question,
    options: options.map((text, index) => ({
      id: index,
      text,
      votes: 0,
    })),
  };
}

export function normalizePoll(thread) {
  if (!thread) return null;

  if (thread.poll?.options?.length) {
    return thread.poll;
  }

  if (thread.pollQuestion && thread.pollOptions?.length) {
    return buildPollObject(thread.pollQuestion, thread.pollOptions);
  }

  return null;
}

export async function saveUserThread({
  forum,
  title,
  body,
  anonymous,
  imageFile,
  documentFile,
  videoLink,
  pollQuestion,
  pollOptions,
}) {
  let image = null;

  if (imageFile && imageFile.size <= 2 * 1024 * 1024) {
    image = await new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(imageFile);
    });
  }

  const poll = buildPollObject(pollQuestion, pollOptions);
  const description = buildDescription(body, {
    videoLink,
    documentName: documentFile?.name || null,
  });

  const thread = {
    id: Date.now(),
    tags: [
      { label: forum, tone: "default" },
      {
        label: anonymous ? "анонимен" : "ти",
        icon: "/Generic avatar.svg",
      },
    ],
    title: title.trim(),
    excerpt: body.trim().slice(0, 160),
    fullBody: description,
    postedAgo: "пред 1мин.",
    ageInDays: 0,
    views: 0,
    votes: 0,
    comments: 0,
    image,
    documentName: documentFile?.name || null,
    videoLink: videoLink?.trim() || null,
    poll,
    forum,
    forumSlug: slugify(forum),
    isUserCreated: true,
    isAnonymous: anonymous,
    createdAt: new Date().toISOString(),
  };

  const threads = getUserThreads();
  threads.unshift(thread);
  localStorage.setItem(STORAGE_KEY, JSON.stringify(threads));

  return thread;
}

export function getUserThreadsForForum(forumSlug) {
  return getUserThreads().filter(
    (thread) =>
      thread.forumSlug === forumSlug || slugify(thread.forum || "") === forumSlug,
  );
}

export function findUserThread(id) {
  const numericId = Number(id);
  return getUserThreads().find((thread) => thread.id === numericId) || null;
}

export function getUserPollVote(threadId) {
  const votesMap = getPollVotesMap();
  return votesMap[`${threadId}_${getVoterId()}`] ?? null;
}

export function voteOnPoll(threadId, optionId) {
  const numericId = Number(threadId);
  const threads = getUserThreads();
  const threadIndex = threads.findIndex((thread) => thread.id === numericId);

  if (threadIndex === -1) return null;

  const thread = { ...threads[threadIndex] };
  const poll = normalizePoll(thread);

  if (!poll) return null;

  const votesMap = getPollVotesMap();
  const voteKey = `${numericId}_${getVoterId()}`;
  const previousOptionId = votesMap[voteKey];

  if (previousOptionId === optionId) {
    return thread;
  }

  poll.options = poll.options.map((option) => ({ ...option }));

  if (previousOptionId !== undefined && previousOptionId !== null) {
    const previousOption = poll.options.find((option) => option.id === previousOptionId);
    if (previousOption && previousOption.votes > 0) {
      previousOption.votes -= 1;
    }
  }

  const selectedOption = poll.options.find((option) => option.id === optionId);
  if (!selectedOption) return null;

  selectedOption.votes += 1;
  votesMap[voteKey] = optionId;

  thread.poll = poll;
  threads[threadIndex] = thread;

  localStorage.setItem(STORAGE_KEY, JSON.stringify(threads));
  savePollVotesMap(votesMap);

  return thread;
}

export function deleteUserThread(id) {
  const numericId = Number(id);
  const threads = getUserThreads().filter((thread) => thread.id !== numericId);
  localStorage.setItem(STORAGE_KEY, JSON.stringify(threads));

  const votesMap = getPollVotesMap();
  const voterId = getVoterId();
  Object.keys(votesMap).forEach((key) => {
    if (key.startsWith(`${numericId}_${voterId}`) || key.startsWith(`${numericId}_`)) {
      delete votesMap[key];
    }
  });
  savePollVotesMap(votesMap);

  return true;
}

export function isUserCreatedThread(id) {
  return Boolean(findUserThread(id));
}
