"use client";

import { useEffect, useRef, useState } from "react";
import Avatar from "@/components/ui/Avatar";
import CommentActions from "@/components/thread/CommentActions";
import CommentAuthor from "@/components/thread/CommentAuthor";
import CommentBody from "@/components/thread/CommentBody";
import CommentComposer from "@/components/thread/CommentComposer";
import EditCommentDialog from "@/components/thread/EditCommentDialog";
import { deleteComment, getCommentReplies, updateComment } from "@/api/comments";
import { reportComment, reportErrorMessage } from "@/api/moderation";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import ReportDialog from "@/components/ui/ReportDialog";
import { useCommentShift } from "@/hooks/useCommentShift";
import { useHashTarget } from "@/hooks/useHashTarget";
import { useProfile } from "@/hooks/useProfile";
import { userFacingError } from "@/lib/api";
import { isActivelyBanned } from "@/lib/ban";
import { formatEditedOrPostedAgo } from "@/lib/time";

// Tailwind gi chita klasite staticki, pa sekoja varijanta e cela niza.
// "phone" vazhi samo pod md, "tablet" pod lg, "all" na sekoj ekran.
const REPLIES = {
  nested: "col-start-2",
  phone: "col-start-1 col-end-3 md:col-start-2",
  tablet: "col-start-1 col-end-3 lg:col-start-2",
  all: "col-start-1 col-end-3",
};

// Povlecheniot komentar ne ja crta linijata — ja ima veke od najgorniot vo
// kolonata — a prstenot vo bojata na karticata mu pravi mesto na avatarot.
const PULLED = {
  phone: {
    line: "max-md:hidden",
    avatar: "max-md:rounded-full max-md:ring-4 max-md:ring-white max-md:group-hover:ring-gray-50",
  },
  tablet: {
    line: "max-lg:hidden",
    avatar: "max-lg:rounded-full max-lg:ring-4 max-lg:ring-white max-lg:group-hover:ring-gray-50",
  },
  all: {
    line: "hidden",
    avatar: "rounded-full ring-4 ring-white group-hover:ring-gray-50",
  },
};

export default function Comment({
  comment,
  threadId,
  isAnonymousThread = false,
  isThreadOwner = false,
  onCommentCreated,
  onCommentDeleted,
  onCommentUpdated,
  expandPath = null,
  preloadedReplies = null,
  depth = 0,
}) {
  const { user } = useProfile();
  const elementId = `comment-${comment.id}`;
  const containerRef = useRef(null);
  const autoExpandedRef = useRef(false);
  const linked = useHashTarget(elementId);
  // Patekata do spodeleniot odgovor: ako pochnuva so mene, ostatokot odi podolu.
  const onPath = expandPath?.[0] === comment.id;
  const childExpandPath = onPath ? expandPath.slice(1) : null;
  const [content, setContent] = useState(comment.content ?? "");
  const [mentions, setMentions] = useState(comment.mentions ?? []);
  const [editedAt, setEditedAt] = useState(comment.edited_at ?? null);
  // Prazna sodrzhina bez GIF znachi izbrishan komentar shto ostanal zaradi odgovorite.
  const [deleted, setDeleted] = useState(!comment.content && !comment.gif_url);
  const [expanded, setExpanded] = useState(false);
  const [replies, setReplies] = useState([]);
  const [repliesCount, setRepliesCount] = useState(comment.replies_count ?? 0);
  const [loadingReplies, setLoadingReplies] = useState(false);
  const [replying, setReplying] = useState(false);
  const [reporting, setReporting] = useState(false);
  const [reported, setReported] = useState(false);
  const [editing, setEditing] = useState(false);
  const [saved, setSaved] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleteDone, setDeleteDone] = useState(false);
  const [busy, setBusy] = useState(false);
  const [actionError, setActionError] = useState("");
  const hasReplies = repliesCount > 0;
  const showThread = expanded && (replies.length > 0 || loadingReplies);
  const showLine = hasReplies || depth > 0 || showThread;
  // Nivoata shto ispadnale od prozorecot ja delat kolonata so roditelot —
  // komentarot si ostanuva, samo veke ne se vleche nadesno. Linijata na
  // kolonata ja crta najgorniot, pa povlechenite ne ja povtoruvaat.
  const { merged, pulled } = useCommentShift(depth, showThread);
  const pulledStyle = pulled ? PULLED[pulled] : null;
  // Na anonimna diskusija avtorot e skrien, pa nejziniot sopstvenik gi
  // prepoznava svoite komentari preku is_owner na samata diskusija.
  const isOwn = comment.author
    ? user != null && comment.author.id === user.id
    : isAnonymousThread && isThreadOwner;
  const canManage = isOwn && !deleted;
  const canReply = !isActivelyBanned(user);

  useEffect(() => {
    if (!linked) return;

    containerRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
  }, [linked]);

  useEffect(() => {
    if (!onPath || autoExpandedRef.current) return;

    autoExpandedRef.current = true;
    setExpanded(true);
    loadReplies({ allowPreloaded: true });
    // Se izvrshuva ednash po pateka, pa ostanatite vrednosti ne se zavisnosti.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [onPath]);

  // allowPreloaded: baranjeto na spodeleniot komentar gi vchita ovie odgovori
  // po pat, pa nema potreba povtorno da se zemaat od serverot.
  async function loadReplies({ allowPreloaded = false } = {}) {
    const preloaded = allowPreloaded ? preloadedReplies?.[comment.id] : null;

    if (preloaded) {
      setReplies(preloaded);
      setRepliesCount(preloaded.length);
      return preloaded.length;
    }

    setLoadingReplies(true);
    try {
      const next = await getCommentReplies(comment.id);
      setReplies(next);
      setRepliesCount(next.length);
      return next.length;
    } catch {
      setReplies([]);
      return null;
    } finally {
      setLoadingReplies(false);
    }
  }

  async function toggleReplies() {
    if (loadingReplies) return;

    if (expanded) {
      setExpanded(false);
      return;
    }

    setExpanded(true);
    if (replies.length === 0 && repliesCount > 0) {
      await loadReplies();
    }
  }

  async function handleReplyCreated(created) {
    onCommentCreated?.(created);
    setReplying(false);
    setRepliesCount((count) => count + 1);
    setExpanded(true);

    const total = await loadReplies();
    if (total !== null) {
      onCommentUpdated?.(comment.id, { replies_count: total });
    }
  }

  // "Сокриј одговори" gi unmount-ira decata, pa promenite mora da se zachuvaat
  // ovde — inache pri povtorno otvoranje se vrakja starata sodrzhina.
  function patchReply(replyId, patch) {
    setReplies((prev) =>
      prev.map((reply) => (reply.id === replyId ? { ...reply, ...patch } : reply)),
    );
  }

  function handleReplyDeleted(replyId) {
    patchReply(replyId, { content: "", mentions: [], gif_url: null });
    onCommentDeleted?.(replyId);
  }

  async function handleSave({ content: next }) {
    const updated = await updateComment(comment.id, { content: next });
    const patch = {
      content: updated.content,
      mentions: updated.mentions ?? [],
      edited_at: updated.edited_at ?? null,
    };

    setContent(patch.content);
    setMentions(patch.mentions);
    setEditedAt(patch.edited_at);
    setEditing(false);
    setSaved(true);
    onCommentUpdated?.(comment.id, patch);
  }

  async function handleConfirmDelete() {
    if (busy) return;

    setBusy(true);
    setActionError("");

    try {
      await deleteComment(comment.id);
      setConfirmingDelete(false);
      setDeleted(true);
      setContent("");
      setMentions([]);
      setDeleteDone(true);
      onCommentDeleted?.(comment.id);
    } catch (err) {
      setActionError(userFacingError(err, "Неуспешно бришење. Обиди се повторно."));
      setConfirmingDelete(false);
    } finally {
      setBusy(false);
    }
  }

  async function handleReport({ reason, details }) {
    try {
      await reportComment(comment.id, { reason, details });
      setReporting(false);
      setReported(true);
    } catch (error) {
      const next = new Error(reportErrorMessage(error));
      next.status = error?.status;
      next.body = error?.body;
      throw next;
    }
  }

  return (
    <div
      id={elementId}
      ref={containerRef}
      className="grid min-w-0 scroll-mt-24 grid-cols-[auto_minmax(0,1fr)] gap-x-2"
    >
      {/* Avatarot ja drzhi prvata kolona, pa vlekata na odgovorite doagja od
          samata reshetka — povlechenite prosto sedat vo dvete koloni. */}
      <div className="col-start-1 row-start-1 row-end-3 flex flex-col items-center">
        <div className={pulledStyle?.avatar ?? ""}>
          <Avatar src={comment.author?.imageUrl} size="md" />
        </div>
        {showLine ? (
          <div
            className={`mt-1 w-0.5 flex-1 rounded-xs bg-[#CFE9ED] ${
              pulledStyle?.line ?? ""
            }`}
          />
        ) : null}
      </div>

      <div className="col-start-2 row-start-1 flex min-w-0 flex-col gap-3">
        {/* Osvetluvanjeto go fakja samo ovoj komentar — odgovorite se nadvor od
            ovoj blok. Negativnata margina go ponishtuva paddingot, bez skok. */}
        <div
          className={`flex min-w-0 flex-col gap-3 transition-colors ${
            linked ? "-m-2 rounded-2xl bg-[#F1EEFE] p-2 ring-1 ring-[#582FF5]/25" : ""
          }`}
        >
          <CommentAuthor author={comment.author} />
          {deleted ? (
            <p className="text-[14px] italic leading-[22px] text-[#999999]">
              Коментарот е избришан.
            </p>
          ) : (
            <>
              {content ? (
                <CommentBody text={content} mentions={mentions} muted={depth === 0} />
              ) : null}
              {comment.gif_url ? (
                <img src={comment.gif_url} alt="GIF" className="max-w-60 rounded-xl" />
              ) : null}
            </>
          )}
          <CommentActions
            commentId={comment.id}
            votes={comment.upvotes}
            hasVoted={comment.has_voted}
            repliesCount={repliesCount}
            expanded={expanded}
            loadingReplies={loadingReplies}
            deleted={deleted}
            isOwner={canManage}
            canReply={canReply}
            onToggle={toggleReplies}
            onReply={() => {
              if (!canReply) return;
              setReplying((open) => !open);
            }}
            onReport={() => setReporting(true)}
            onEdit={() => {
              setActionError("");
              setEditing(true);
            }}
            onDelete={() => {
              setActionError("");
              setConfirmingDelete(true);
            }}
            onVoted={(next) => onCommentUpdated?.(comment.id, next)}
            createdAtLabel={formatEditedOrPostedAgo({
              created_at: comment.created_at,
              edited_at: editedAt,
            })}
          />
        </div>

        {actionError ? (
          <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#DC2626]">
            {actionError}
          </p>
        ) : null}

        {editing && (
          <EditCommentDialog
            open
            comment={{ ...comment, content }}
            onClose={() => setEditing(false)}
            onSave={handleSave}
          />
        )}

        <InfoDialog
          open={saved}
          title="Промените беа успешно зачувани."
          onClose={() => setSaved(false)}
        />

        <ConfirmDialog
          open={confirmingDelete}
          title="Дали си сигурен дека сакаш да го избришеш овој коментар?"
          confirmLabel={busy ? "Се брише…" : "Избриши"}
          onCancel={() => {
            if (!busy) setConfirmingDelete(false);
          }}
          onConfirm={handleConfirmDelete}
        />

        <InfoDialog
          open={deleteDone}
          title="Коментарот беше успешно избришан."
          onClose={() => setDeleteDone(false)}
        />

        {reporting && (
          <ReportDialog
            open
            onClose={() => setReporting(false)}
            onSubmit={handleReport}
          />
        )}

        <InfoDialog
          open={reported}
          title="Пријавата беше успешно поднесена и испратена до админот."
          onClose={() => setReported(false)}
        />

        {replying && canReply ? (
          <CommentComposer
            compact
            threadId={threadId}
            parentId={comment.id}
            isAnonymousThread={isAnonymousThread}
            isThreadOwner={isThreadOwner}
            onClose={() => setReplying(false)}
            onCreated={handleReplyCreated}
          />
        ) : null}
      </div>

      {showThread ? (
        <div
          className={`row-start-2 flex min-w-0 flex-col gap-4 pt-4 ${
            REPLIES[merged ?? "nested"]
          }`}
        >
          {loadingReplies && replies.length === 0 ? (
            <p className="text-[13px] text-[#999999]">Се вчитуваат одговорите…</p>
          ) : (
            replies.map((reply) => (
              <Comment
                key={reply.id}
                comment={reply}
                threadId={threadId}
                isAnonymousThread={isAnonymousThread}
                isThreadOwner={isThreadOwner}
                onCommentCreated={onCommentCreated}
                onCommentDeleted={handleReplyDeleted}
                onCommentUpdated={patchReply}
                expandPath={childExpandPath}
                preloadedReplies={preloadedReplies}
                depth={depth + 1}
              />
            ))
          )}
        </div>
      ) : null}
    </div>
  );
}
